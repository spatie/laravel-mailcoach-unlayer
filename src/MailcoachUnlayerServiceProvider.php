<?php

namespace Spatie\MailcoachUnlayer;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spatie\Mailcoach\Mailcoach;

class MailcoachUnlayerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mailcoach-unlayer');
        $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/views' => base_path('resources/views/vendor/mailcoach-unlayer'),
            ], 'mailcoach-unlayer-views');
        }

        Livewire::component('mailcoach-unlayer::editor', UnlayerEditor::class);

        Mailcoach::editorScript(UnlayerEditor::class, 'https://editor.unlayer.com/embed.js');
    }
}
