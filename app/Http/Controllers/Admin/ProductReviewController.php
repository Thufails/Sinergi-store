<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = ProductReview::query()
            ->with(['customer', 'product', 'orderItem.order'])
            ->when($request->status === 'pending', fn ($query) => $query->where('is_approved', false))
            ->when($request->status === 'approved', fn ($query) => $query->where('is_approved', true))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.product-reviews.index', compact('reviews'));
    }

    public function updateApproval(Request $request, ProductReview $productReview): RedirectResponse
    {
        $validated = $request->validate([
            'is_approved' => ['required', 'boolean'],
        ]);

        $productReview->update([
            'is_approved' => (bool) $validated['is_approved'],
        ]);

        return redirect()
            ->route('admin.product-reviews.index')
            ->with('success', $productReview->is_approved
                ? 'Ulasan produk disetujui dan dapat ditampilkan.'
                : 'Ulasan produk disembunyikan dari halaman produk.');
    }

    public function destroy(ProductReview $productReview): RedirectResponse
    {
        Storage::disk('public')->delete($productReview->media_paths ?? []);
        $productReview->delete();

        return redirect()
            ->route('admin.product-reviews.index')
            ->with('success', 'Ulasan produk berhasil dihapus.');
    }
}
