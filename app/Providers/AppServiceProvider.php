<?php

namespace App\Providers;

use App\Models\ProductConversation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.admin', function (\Illuminate\View\View $view): void {
            $unreadChatConversations = auth()->check()
                ? ProductConversation::query()
                    ->whereHas('messages', fn (Builder $query) => $query
                        ->where('sender_type', 'customer')
                        ->whereNull('read_at'))
                    ->count()
                : 0;

            $view->with('unreadChatConversations', $unreadChatConversations);
        });
    }
}
