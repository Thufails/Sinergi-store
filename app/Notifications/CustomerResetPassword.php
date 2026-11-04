<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CustomerResetPassword extends ResetPassword
{
    /**
     * Build the branded customer password reset email.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Atur ulang password Sinergi Store')
            ->view('emails.auth.customer-password-reset', [
                'customerName' => $notifiable->name,
                'resetUrl' => $this->resetUrl($notifiable),
                'expireMinutes' => config('auth.passwords.customers.expire'),
            ]);
    }

    /**
     * Build the customer-facing password reset URL.
     *
     * @param  mixed  $notifiable
     */
    protected function resetUrl($notifiable): string
    {
        return url(route('account.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
