<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerOrderController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $orders = $customer->orders()
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('store.account.orders.index', compact('orders'));
    }

    public function show(Request $request, string $order): View
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $order = $customer->orders()
            ->with(['items.review', 'statusHistories', 'customerAddress'])
            ->whereKey($order)
            ->firstOrFail();

        return view('store.account.orders.show', compact('order'));
    }

    public function confirmReceipt(Request $request, string $order): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $order = $customer->orders()->whereKey($order)->firstOrFail();

        $result = DB::transaction(function () use ($customer, $order): ?string {
            $lockedOrder = $customer->orders()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->status === Order::STATUS_COMPLETED && $lockedOrder->receipt_confirmed_at) {
                return null;
            }

            if ($lockedOrder->status !== Order::STATUS_SHIPPED) {
                return 'Penerimaan hanya dapat dikonfirmasi untuk pesanan yang sudah dikirim.';
            }

            $lockedOrder->update([
                'status' => Order::STATUS_COMPLETED,
                'receipt_confirmed_at' => now(),
            ]);

            $lockedOrder->statusHistories()->create([
                'from_status' => Order::STATUS_SHIPPED,
                'to_status' => Order::STATUS_COMPLETED,
                'actor_name' => 'Pembeli (' . $customer->name . ')',
                'message' => 'Pembeli mengonfirmasi barang telah diterima.',
            ]);

            return null;
        });

        if ($result !== null) {
            return back()->with('error', $result);
        }

        return redirect()
            ->route('account.orders.reviews.create', $order)
            ->with('success', 'Penerimaan pesanan dikonfirmasi. Silakan beri ulasan produk jika kamu mau.');
    }

    public function createReviews(Request $request, string $order): View
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $order = $customer->orders()
            ->with(['items.product', 'items.review'])
            ->whereKey($order)
            ->firstOrFail();

        abort_unless(
            $order->status === Order::STATUS_COMPLETED && $order->receipt_confirmed_at,
            404,
        );

        return view('store.account.orders.reviews', compact('order'));
    }

    public function storeReviews(Request $request, string $order): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $order = $customer->orders()
            ->with(['items.review'])
            ->whereKey($order)
            ->firstOrFail();

        abort_unless(
            $order->status === Order::STATUS_COMPLETED && $order->receipt_confirmed_at,
            404,
        );

        $validated = $request->validate([
            'reviews' => ['nullable', 'array'],
            'reviews.*.rating' => ['nullable', 'integer', 'between:1,5'],
            'reviews.*.comment' => ['nullable', 'string', 'max:2000'],
            'reviews.*.media' => ['nullable', 'array', 'max:5'],
            'reviews.*.media.*' => ['file', 'mimes:jpg,jpeg,png,webp,mp4,mov,webm', 'max:20480'],
        ], [
            'reviews.*.rating.between' => 'Rating produk harus antara 1 sampai 5.',
            'reviews.*.media.max' => 'Maksimal 5 foto atau video untuk setiap produk.',
            'reviews.*.media.*.mimes' => 'Media harus berupa JPG, PNG, WEBP, MP4, MOV, atau WEBM.',
            'reviews.*.media.*.max' => 'Ukuran setiap foto atau video maksimal 20 MB.',
        ]);

        $orderItems = $order->items
            ->whereNotNull('product_id')
            ->keyBy(fn (OrderItem $item): string => (string) $item->getKey());
        $reviewInputs = $validated['reviews'] ?? [];
        $mediaPaths = [];
        $reviewsToCreate = [];

        try {
            foreach ($reviewInputs as $itemId => $reviewInput) {
                $orderItem = $orderItems->get((string) $itemId);
                abort_if($orderItem === null, 404);

                $uploadedMedia = $reviewInput['media'] ?? [];
                $rating = $reviewInput['rating'] ?? null;
                $comment = trim((string) ($reviewInput['comment'] ?? ''));

                if ($rating === null && ($comment !== '' || $uploadedMedia !== [])) {
                    throw ValidationException::withMessages([
                        "reviews.{$itemId}.rating" => 'Pilih rating sebelum mengirim komentar atau media.',
                    ]);
                }

                if ($rating === null || $orderItem->review !== null) {
                    continue;
                }

                $storedPaths = [];
                foreach ($uploadedMedia as $file) {
                    $path = $file->store("product-reviews/{$customer->id}/{$order->id}/{$itemId}", 'public');
                    $mediaPaths[] = $path;
                    $storedPaths[] = $path;
                }

                $reviewsToCreate[] = [
                    'customer_id' => $customer->id,
                    'product_id' => $orderItem->product_id,
                    'order_item_id' => $orderItem->id,
                    'rating' => (int) $rating,
                    'comment' => $comment !== '' ? $comment : null,
                    'media_paths' => $storedPaths !== [] ? $storedPaths : null,
                    'is_approved' => true,
                ];
            }

            DB::transaction(function () use ($reviewsToCreate): void {
                foreach ($reviewsToCreate as $reviewData) {
                    ProductReview::create($reviewData);
                }
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($mediaPaths);

            throw $exception;
        }

        return redirect()
            ->route('account.orders.show', $order)
            ->with('success', $reviewsToCreate === []
                ? 'Kamu bisa memberikan ulasan kapan saja dari detail pesanan.'
                : 'Ulasanmu berhasil dikirim dan langsung tampil di halaman produk.');
    }

    public function uploadPaymentProof(Request $request, string $order): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $order = $customer->orders()->whereKey($order)->firstOrFail();

        $validated = $request->validate([
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'payment_proof.mimes' => 'Bukti pembayaran harus berupa JPG, PNG, WEBP, atau PDF.',
            'payment_proof.max' => 'Ukuran bukti pembayaran maksimal 5 MB.',
        ]);

        if ($order->status !== Order::STATUS_AWAITING_PAYMENT) {
            throw ValidationException::withMessages([
                'payment_proof' => 'Bukti pembayaran hanya dapat dikirim saat pesanan menunggu pembayaran.',
            ]);
        }

        if (! $order->payment_due_at || $order->payment_due_at->isPast()) {
            throw ValidationException::withMessages([
                'payment_proof' => 'Batas pembayaran sudah lewat. Hubungi admin untuk bantuan.',
            ]);
        }

        $newProofPath = $validated['payment_proof']->store('payment-proofs/' . $customer->id, 'local');
        $oldProofPath = null;
        $message = DB::transaction(function () use ($customer, $order, $newProofPath, &$oldProofPath): ?string {
            $lockedOrder = $customer->orders()->lockForUpdate()->findOrFail($order->id);

            if (
                $lockedOrder->status !== Order::STATUS_AWAITING_PAYMENT
                || ! $lockedOrder->payment_due_at
                || $lockedOrder->payment_due_at->isPast()
            ) {
                return 'Pesanan tidak lagi menunggu pembayaran atau batas waktunya sudah lewat.';
            }

            $previousStatus = $lockedOrder->status;
            $oldProofPath = $lockedOrder->payment_proof_path;
            $lockedOrder->update([
                'payment_proof_path' => $newProofPath,
                'payment_proof_uploaded_at' => now(),
                'payment_review_note' => null,
                'status' => Order::STATUS_PAYMENT_REVIEW,
            ]);
            $lockedOrder->statusHistories()->create([
                'from_status' => $previousStatus,
                'to_status' => Order::STATUS_PAYMENT_REVIEW,
                'actor_name' => $customer->name,
            ]);

            return null;
        });

        if ($message !== null) {
            Storage::disk('local')->delete($newProofPath);

            return back()->withErrors(['payment_proof' => $message]);
        }

        if ($oldProofPath) {
            Storage::disk('local')->delete($oldProofPath);
        }

        return redirect()->route('account.orders.show', $order)
            ->with('success', 'Bukti pembayaran berhasil dikirim dan sedang menunggu verifikasi admin.');
    }

    public function showPaymentProof(Request $request, string $order): BinaryFileResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $order = $customer->orders()->whereKey($order)->firstOrFail();

        abort_unless($order->payment_proof_path && Storage::disk('local')->exists($order->payment_proof_path), 404);

        return response()->file(Storage::disk('local')->path($order->payment_proof_path), [
            'Content-Type' => Storage::disk('local')->mimeType($order->payment_proof_path) ?? 'application/octet-stream',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
