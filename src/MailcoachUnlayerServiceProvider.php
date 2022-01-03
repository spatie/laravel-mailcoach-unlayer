<?php

namespace Spatie\MailcoachUnlayer;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MailcoachUnlayerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mailcoach-unlayer');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/views' => base_path('resources/views/vendor/mailcoach/unlayer'),
            ], 'mailcoach-unlayer-views');

            if (! class_exists('CreateMailcoachUnlayerTables')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_mailcoach_unlayer_tables.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_mailcoach_unlayer_tables.php'),
                ], 'mailcoach-unlayer-migrations');
            }
        }

        Route::macro('mailcoachUnlayer', function (string $url = '') {
            Route::prefix($url)->group(function () {
                $middlewareClasses = config('mailcoach.middleware.web', []);

                Route::middleware($middlewareClasses)->prefix('')->group(__DIR__ . '/../routes/api.php');
            });
        });
    }
}
