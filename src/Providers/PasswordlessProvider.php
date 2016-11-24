<?php

namespace Whyounes\Passwordless\Providers;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class PasswordlessProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../migrations');
        //$this->loadTranslationsFrom(__DIR__.'/../../lang', 'passwordless');
        $this->publishes(
            [
                __DIR__.'/../../config/passwordless.php' => config_path('passwordless.php'),
                __DIR__.'/../../lang'                    => resource_path('lang/vendor/passwordless'),
            ]
        );
    }

    /**
     * Regsiter application components
     */
    public function register()
    {
        $this->registerEvents();
    }

    /**
     * Register application event listeners
     */
    public function registerEvents()
    {
        // Delete user tokens after login
        if (config('passwordless.empty_tokens_after_login') === true) {
            Event::listen(
                Authenticated::class,
                function ($event) {
                    \Log::info($event->user->id);
                    $event->user->tokens()
                        ->delete();
                }
            );
        }
    }
}