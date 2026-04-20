<?php

namespace Corepine\Modal;

use Corepine\Modal\Livewire\ModalHost;
use Corepine\Modal\Support\ModalConfig;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ModalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/corepine-modal.php', 'corepine-modal');

        $this->app->singleton(ModalConfig::class, static fn (): ModalConfig => new ModalConfig);
        $this->app->singleton(ModalService::class, static fn ($app): ModalService => new ModalService(
            $app->make(ModalConfig::class)
        ));
        $this->app->alias(ModalService::class, 'corepine-modal.service');
        $this->app->alias(ModalService::class, \Corepine\Modal\Facades\Modal::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'corepine-modal');

        if (class_exists(Livewire::class)) {
            Livewire::component('corepine-modal', ModalHost::class);
        }

        Blade::component('corepine-modal::components.assets', 'corepine-modal');
        Blade::component('corepine-modal::components.modal', 'corepine.modal');
        Blade::component('corepine-modal::components.layout', 'corepine-modal-layout');
        Blade::component('corepine-modal::components.layout', 'corepine-modal-template');
        Blade::component('corepine-modal::components.footer', 'corepine-modal-footer');
        Blade::component('corepine-modal::components.actions.open', 'corepine-modal-actions-open');
        Blade::component('corepine-modal::components.actions.close', 'corepine-modal-actions-close');
        
        Blade::component('corepine-modal::components.assets', 'corepine.modal.assets');
        Blade::component('corepine-modal::components.layout', 'corepine.modal.layout');
        Blade::component('corepine-modal::components.layout', 'corepine.modal.template');
        Blade::component('corepine-modal::components.footer', 'corepine.modal.footer');
        Blade::component('corepine-modal::components.actions.open', 'corepine.modal.actions.open');
        Blade::component('corepine-modal::components.actions.close', 'corepine.modal.actions.close');

        Blade::directive('corepineModal', static fn () => "<?php echo app('view')->make('corepine-modal::components.assets')->render(); ?>");

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/corepine-modal.php' => config_path('corepine-modal.php'),
            ], 'corepine-modal-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/corepine-modal'),
            ], 'corepine-modal-views');

            $this->publishes([
                __DIR__.'/../resources/css/app.css' => resource_path('css/vendor/corepine-modal.css'),
            ], 'corepine-modal-assets');
        }
    }
}
