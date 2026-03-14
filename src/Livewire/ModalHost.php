<?php

namespace Corepine\Modal\Livewire;

use Corepine\Modal\Support\ModalConfig;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Reflector;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Livewire\Component;
use Throwable;

class ModalHost extends Component
{
    public array $modals = [];

    public array $stack = [];

    public ?string $activeModalId = null;

    public function getListeners(): array
    {
        return $this->modalConfig()->listenersMap([
            'open' => 'openModal',
            'close' => 'closeModal',
            'close_top' => 'closeTopModal',
            'close_all' => 'closeAllModals',
            'destroy' => 'destroyModal',
            'reset' => 'resetState',
        ]);
    }

    public function openModal(string $component, array $arguments = [], array $modalAttributes = []): void
    {
        $componentClass = $this->resolveComponentClass($component);
        $componentName = $this->getComponentName($componentClass);
        $id = (string) Str::ulid();

        $arguments = collect($arguments)
            ->merge($this->resolveComponentProps($arguments, app()->make($componentClass)))
            ->all();

        $componentOverrides = method_exists($componentClass, 'modalAttributes')
            ? (array) $componentClass::modalAttributes()
            : [];

        if (
            method_exists($componentClass, 'modalSize')
            && ! array_key_exists('size', $componentOverrides)
            && ! array_key_exists('width', $componentOverrides)
            && ! array_key_exists('maxWidth', $componentOverrides)
            && ! array_key_exists('sizeClass', $componentOverrides)
            && ! array_key_exists('sizeClasses', $componentOverrides)
        ) {
            $size = $componentClass::modalSize();

            if (is_string($size) && $size !== '') {
                $componentOverrides['size'] = $size;
            }
        }

        $this->modals[$id] = [
            'id' => $id,
            'name' => $componentName,
            'class' => $componentClass,
            'arguments' => $arguments,
            'modalAttributes' => $this->modalConfig()->mergedModalAttributes($componentOverrides, $modalAttributes),
        ];

        $this->stack[] = $id;
        $this->activeModalId = $id;

        $this->dispatch(
            $this->modalConfig()->dispatchEvent('opened'),
            id: $id,
            name: $componentName,
            class: $componentClass
        );

        $this->dispatch(
            $this->modalConfig()->dispatchEvent('changed'),
            id: $this->activeModalId,
            stack: $this->stack
        );
    }

    public function closeModal(?string $id = null, int $count = 1, bool $destroy = true, bool $force = false): void
    {
        if ($force) {
            $this->closeAllModals($destroy);

            return;
        }

        if ($id === null) {
            $this->closeTopModal($count, $destroy);

            return;
        }

        $position = array_search($id, $this->stack, true);

        if ($position === false) {
            return;
        }

        $layersToClose = count($this->stack) - $position;
        $this->closeTopModal($layersToClose, $destroy);
    }

    public function closeTopModal(int $count = 1, bool $destroy = true, bool $force = false): void
    {
        if ($force) {
            $this->closeAllModals($destroy);

            return;
        }

        $count = max(1, $count);

        for ($i = 0; $i < $count; $i++) {
            $id = array_pop($this->stack);

            if ($id === null) {
                break;
            }

            $modal = $this->modals[$id] ?? null;

            if ($modal !== null) {
                $this->dispatch($this->modalConfig()->dispatchEvent('closed'), id: $id, name: $modal['name']);

                if (($modal['modalAttributes']['dispatchCloseEvent'] ?? false) === true) {
                    $this->dispatch($this->modalConfig()->dispatchEvent('component_closed'), id: $id, name: $modal['name']);
                }
            }

            if ($destroy) {
                unset($this->modals[$id]);
            }
        }

        $this->setCurrentActiveModal();
    }

    public function closeAllModals(bool $destroy = true): void
    {
        foreach (array_reverse($this->stack) as $id) {
            $modal = $this->modals[$id] ?? null;

            if ($modal === null) {
                continue;
            }

            $this->dispatch($this->modalConfig()->dispatchEvent('closed'), id: $id, name: $modal['name']);

            if (($modal['modalAttributes']['dispatchCloseEvent'] ?? false) === true) {
                $this->dispatch($this->modalConfig()->dispatchEvent('component_closed'), id: $id, name: $modal['name']);
            }

            if ($destroy) {
                unset($this->modals[$id]);
            }
        }

        $this->stack = [];
        $this->activeModalId = null;

        $this->dispatch($this->modalConfig()->dispatchEvent('all_closed'));
        $this->dispatch($this->modalConfig()->dispatchEvent('changed'), id: null, stack: []);
    }

    public function destroyModal(string $id): void
    {
        unset($this->modals[$id]);

        $this->stack = array_values(array_filter(
            $this->stack,
            static fn (string $value): bool => $value !== $id
        ));

        $this->setCurrentActiveModal();
    }

    public function resetState(): void
    {
        $this->modals = [];
        $this->stack = [];
        $this->activeModalId = null;

        $this->dispatch($this->modalConfig()->dispatchEvent('all_closed'));
        $this->dispatch($this->modalConfig()->dispatchEvent('changed'), id: null, stack: []);
    }

    public function resolveComponentProps(array $attributes, Component $component): Collection
    {
        return $this->getPublicPropertyTypes($component)
            ->intersectByKeys($attributes)
            ->map(fn (string $className, string $propName) => $this->resolveParameter($attributes, $propName, $className));
    }

    public function getPublicPropertyTypes(Component $component): Collection
    {
        return collect($component->all())
            ->map(fn ($value, string $name) => Reflector::getParameterClassName(new \ReflectionProperty($component, $name)))
            ->filter();
    }

    protected function resolveComponentClass(string $component): string
    {
        if (class_exists($component) && is_subclass_of($component, Component::class)) {
            return $component;
        }

        if (class_exists(\Livewire\Mechanisms\ComponentRegistry::class)
            && app()->bound(\Livewire\Mechanisms\ComponentRegistry::class)) {
            try {
                return app(\Livewire\Mechanisms\ComponentRegistry::class)->getClass($component);
            } catch (Throwable) {
                // Try finder fallback.
            }
        }

        if (app()->bound('livewire.finder')) {
            try {
                return app('livewire.finder')->resolveClassComponentClassName($component);
            } catch (Throwable) {
                // Handled below.
            }
        }

        throw new InvalidArgumentException("Unable to resolve Livewire modal component [{$component}].");
    }

    protected function getComponentName(string $class): string
    {
        if (class_exists(\Livewire\Mechanisms\ComponentRegistry::class)
            && app()->bound(\Livewire\Mechanisms\ComponentRegistry::class)) {
            try {
                return app(\Livewire\Mechanisms\ComponentRegistry::class)->getName($class);
            } catch (Throwable) {
                // Fall through.
            }
        }

        if (app()->bound('livewire.finder')) {
            try {
                return app('livewire.finder')->normalizeName($class);
            } catch (Throwable) {
                // Fall through.
            }
        }

        return $class;
    }

    protected function resolveParameter(array $attributes, string $parameterName, string $parameterClassName): mixed
    {
        $parameterValue = $attributes[$parameterName];

        if ($parameterValue instanceof UrlRoutable) {
            return $parameterValue;
        }

        if (enum_exists($parameterClassName)) {
            $enum = $parameterClassName::tryFrom($parameterValue);

            if ($enum !== null) {
                return $enum;
            }
        }

        try {
            $instance = app()->make($parameterClassName);
        } catch (Throwable) {
            return $parameterValue;
        }

        if (! $instance instanceof UrlRoutable) {
            return $parameterValue;
        }

        $model = $instance->resolveRouteBinding($parameterValue);

        if ($model === null) {
            throw (new ModelNotFoundException)->setModel(get_class($instance), [$parameterValue]);
        }

        return $model;
    }

    protected function setCurrentActiveModal(): void
    {
        $next = end($this->stack);
        $this->activeModalId = $next === false ? null : $next;

        if ($this->activeModalId === null) {
            $this->dispatch($this->modalConfig()->dispatchEvent('all_closed'));
        }

        $this->dispatch(
            $this->modalConfig()->dispatchEvent('changed'),
            id: $this->activeModalId,
            stack: $this->stack
        );
    }

    protected function modalConfig(): ModalConfig
    {
        return app(ModalConfig::class);
    }

    public function render()
    {
        return view('corepine-modal::livewire.modal-host');
    }
}
