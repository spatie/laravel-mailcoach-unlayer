<?php

namespace Spatie\MailcoachUnlayer\Tests;

use CreateMailcoachTables;
use CreateMailcoachUnlayerTables;
use CreateMediaTable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Route;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Mailcoach\MailcoachServiceProvider;
use Spatie\MailcoachUnlayer\MailcoachUnlayerServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Spatie\\Mailcoach\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        Route::mailcoachUnlayer('mailcoachUnlayer');

        $this->withoutExceptionHandling();
    }

    protected function getPackageProviders($app)
    {
        return [
            MediaLibraryServiceProvider::class,
            MailcoachServiceProvider::class,
            MailcoachUnlayerServiceProvider::class,
            LivewireServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        include_once __DIR__.'/../../../vendor/spatie/laravel-mailcoach/database/migrations/create_mailcoach_tables.php.stub';
        (new CreateMailcoachTables())->up();

        include_once __DIR__ . '/../database/migrations/create_mailcoach_unlayer_tables.php.stub';
        (new CreateMailcoachUnlayerTables())->up();

        include_once __DIR__.'/../../../vendor/spatie/laravel-mailcoach/database/migrations/create_media_table.php.stub';
        (new CreateMediaTable())->up();
    }
}
