<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function orders(Request $request): View
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $orders = $customer->orders()
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('store.account.orders.index', compact('orders'));
    }

    public function showOrder(Request $request, string $order): View
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $order = $customer->orders()
            ->with(['items', 'statusHistories', 'customerAddress'])
            ->whereKey($order)
            ->firstOrFail();

        return view('store.account.orders.show', compact('order'));
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

    public function showPaymentProof(Request $request, string $order): \Symfony\Component\HttpFoundation\BinaryFileResponse
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

    public function profile(Request $request): View
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        return view('store.account.profile', compact('customer'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+().\\s-]{8,30}$/'],
        ]);

        $customer->update([
            'name' => trim($validated['name']),
            'phone' => filled($validated['phone'] ?? null) ? trim($validated['phone']) : null,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $validated = $request->validate([
            'current_password' => ['required', 'current_password:customer'],
            'password' => ['required', 'string', 'min:8', 'regex:/^\\S+$/', 'confirmed'],
        ], [
            'password.regex' => 'Password tidak boleh mengandung spasi.',
        ]);

        $customer->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
