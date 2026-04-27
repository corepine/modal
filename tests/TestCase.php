<?php

namespace Corepine\Modal\Tests;

use Corepine\Modal\ModalServiceProvider;
use Corepine\Support\SupportServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\View;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            ModalServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        View::addLocation(__DIR__.'/Fixtures/views');

        tap($app['config'], function (Repository $config): void {
            $config->set('app.debug', true);
            $config->set('app.env', 'testing');
            $config->set('app.key', 'base64:2fl+Ktvkfl+Fuz4Qp/A75G2RTiWVA/ZoKZvp6fiiM10=');
            $config->set('database.default', 'testbench');
            $config->set('database.connections.testbench', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);
        });
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}
