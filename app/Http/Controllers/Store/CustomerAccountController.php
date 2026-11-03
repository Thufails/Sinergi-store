<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            ->with(['items', 'statusHistories'])
            ->whereKey($order)
            ->firstOrFail();

        return view('store.account.orders.show', compact('order'));
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
