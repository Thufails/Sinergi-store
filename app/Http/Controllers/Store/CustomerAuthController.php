<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    public function showRegistrationForm(): View
    {
        return view('store.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+().\\s-]{8,30}$/'],
            'password' => ['required', 'string', 'min:8', 'regex:/^\\S+$/', 'confirmed'],
        ], [
            'password.regex' => 'Password tidak boleh mengandung spasi.',
        ]);

        $customer = Customer::query()->create([
            'name' => trim($validated['name']),
            'email' => Str::lower(trim($validated['email'])),
            'phone' => filled($validated['phone'] ?? null) ? trim($validated['phone']) : null,
            'password' => $validated['password'],
        ]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('store.home')
            ->with('success', 'Akun berhasil dibuat.');
    }

    public function showLoginForm(): View
    {
        return view('store.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password tidak sesuai.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('store.home');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('store.home')
            ->with('success', 'Kamu berhasil keluar dari akun.');
    }

    public function showForgotPasswordForm(): View
    {
        return view('store.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::broker('customers')->sendResetLink([
            'email' => Str::lower(trim($validated['email'])),
        ]);

        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors([
                'email' => 'Tunggu sebentar sebelum meminta tautan reset lagi.',
            ]);
        }

        return back()->with('status', 'Jika email terdaftar, tautan reset akan dikirim ke email tersebut.');
    }

    public function showResetPasswordForm(Request $request, string $token): View
    {
        return view('store.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'regex:/^\\S+$/', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'same:password'],
        ], [
            'password.regex' => 'Password tidak boleh mengandung spasi.',
        ]);

        $status = Password::broker('customers')->reset(
            $validated,
            function (Customer $customer, string $password): void {
                $customer->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($customer));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'Tautan reset tidak valid atau sudah kedaluwarsa.']);
        }

        return redirect()->route('account.login')
            ->with('status', 'Password berhasil diperbarui. Silakan login.');
    }
}
