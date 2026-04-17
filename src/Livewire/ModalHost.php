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
            'open_sheet' => 'openBottomSheet',
            'close' => 'closeModal',
            'close_top' => 'closeTopModal',
            'close_all' => 'closeAllModals',
            'destroy' => 'destroyModal',
            'reset' => 'resetState',
        ]);
    }

    public function openBottomSheet(string|array|null $component = null, array $arguments = [], array $modalAttributes = []): void
    {
        [$component, $arguments, $modalAttributes] = $this->normalizeOpenRequest(
            $component,
            $arguments,
            $modalAttributes
        );

        if ($component === null) {
            return;
        }

        $this->openModal(
            $component,
            $arguments,
            array_replace($modalAttributes, ['type' => 'sheet', 'sheet' => true])
        );
    }

    public function openModal(string|array|null $component = null, array $arguments = [], array $modalAttributes = []): void
    {
        [$component, $arguments, $modalAttributes] = $this->normalizeOpenRequest(
            $component,
            $arguments,
            $modalAttributes
        );

        if ($component === null) {
            return;
        }

        $componentClass = $this->resolveComponentClass($component);
        $componentName = $this->resolveOpenedComponentName($component, $componentClass);
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

    public function closeModal(?string $id = null, int $count = 1, bool $destroy = true, bool $closeAll = false, array $dispatch = [], array $dispatchTo = []): void
    {
        if ($closeAll) {
            $this->closeAllModals($destroy, $dispatch, $dispatchTo);

            return;
        }

        if ($id === null) {
            $this->closeTopModal($count, $destroy, false, $dispatch, $dispatchTo);

            return;
        }

        $position = array_search($id, $this->stack, true);

        if ($position === false) {
            return;
        }

        $layersToClose = count($this->stack) - $position;
        $this->closeTopModal($layersToClose, $destroy, false, $dispatch, $dispatchTo);
    }

    public function closeTopModal(int $count = 1, bool $destroy = true, bool $closeAll = false, array $dispatch = [], array $dispatchTo = []): void
    {
        if ($closeAll) {
            $this->closeAllModals($destroy, $dispatch, $dispatchTo);

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

                $this->dispatchConfiguredCloseEvents($modal);
            }

            if ($destroy) {
                unset($this->modals[$id]);
            }
        }

        $this->dispatchMappedCloseEvents($dispatch, $dispatchTo);
        $this->setCurrentActiveModal();
    }

    public function closeAllModals(bool $destroy = true, array $dispatch = [], array $dispatchTo = []): void
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

            $this->dispatchConfiguredCloseEvents($modal);

            if ($destroy) {
                unset($this->modals[$id]);
            }
        }

        $this->dispatchMappedCloseEvents($dispatch, $dispatchTo);
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

    /**
     * @return array{0: string|null, 1: array<string, mixed>, 2: array<string, mixed>}
     */
    protected function normalizeOpenRequest(string|array|null $component = null, array $arguments = [], array $modalAttributes = []): array
    {
        if (is_array($component)) {
            $arguments = is_array($component['arguments'] ?? null) ? $component['arguments'] : [];
            $modalAttributes = is_array($component['modalAttributes'] ?? null) ? $component['modalAttributes'] : [];
            $component = is_string($component['component'] ?? null) ? trim($component['component']) : null;
        } elseif (is_string($component)) {
            $component = trim($component);
        }

        if (! is_string($component) || $component === '') {
            return [null, [], []];
        }

        return [$component, $arguments, $modalAttributes];
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

        if (app()->bound('livewire.factory')) {
            try {
                return app('livewire.factory')->resolveComponentClass($component);
            } catch (Throwable) {
                // Try additional fallbacks.
            }
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
        if (app()->bound('livewire.factory')) {
            try {
                return app('livewire.factory')->resolveComponentName($class);
            } catch (Throwable) {
                // Fall through.
            }
        }

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

    protected function resolveOpenedComponentName(string $requestedComponent, string $resolvedClass): string
    {
        if (! (class_exists($requestedComponent) && is_subclass_of($requestedComponent, Component::class))) {
            return $requestedComponent;
        }

        return $this->getComponentName($resolvedClass);
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

    /**
     * @param  array<string, mixed>  $modal
     */
    protected function dispatchConfiguredCloseEvents(array $modal): void
    {
        $attributes = is_array($modal['modalAttributes'] ?? null) ? $modal['modalAttributes'] : [];

        $this->dispatchMappedCloseEvents(
            is_array($attributes['dispatch'] ?? null) ? $attributes['dispatch'] : [],
            is_array($attributes['dispatchTo'] ?? null) ? $attributes['dispatchTo'] : [],
        );
    }

    /**
     * @param  array<string, mixed>  $dispatch
     * @param  array<string, mixed>  $dispatchTo
     */
    protected function dispatchMappedCloseEvents(array $dispatch = [], array $dispatchTo = []): void
    {
        foreach ($this->normalizeCloseDispatches($dispatch) as $event => $params) {
            $this->dispatch($event, ...$params);
        }

        foreach ($this->normalizeCloseDispatchTargets($dispatchTo) as $component => $events) {
            foreach ($events as $event => $params) {
                $this->dispatch($event, ...$params)->to($component);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $dispatch
     * @return array<string, array<int|string, mixed>>
     */
    protected function normalizeCloseDispatches(array $dispatch): array
    {
        $normalized = [];

        foreach ($dispatch as $event => $params) {
            if (is_int($event)) {
                if (is_string($params) && trim($params) !== '') {
                    $normalized[trim($params)] = [];
                }

                continue;
            }

            if (! is_string($event) || trim($event) === '') {
                continue;
            }

            $event = trim($event);

            if ($params instanceof \Traversable) {
                $params = iterator_to_array($params);
            }

            if (is_array($params)) {
                $normalized[$event] = $params;

                continue;
            }

            if (is_null($params)) {
                $normalized[$event] = [];

                continue;
            }

            if (is_scalar($params) || $params instanceof \Stringable) {
                $normalized[$event] = [$params];
            }
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $dispatchTo
     * @return array<string, array<string, array<int|string, mixed>>>
     */
    protected function normalizeCloseDispatchTargets(array $dispatchTo): array
    {
        $normalized = [];

        foreach ($dispatchTo as $component => $events) {
            if (! is_string($component) || trim($component) === '') {
                continue;
            }

            if ($events instanceof \Traversable) {
                $events = iterator_to_array($events);
            }

            if (! is_array($events)) {
                continue;
            }

            $component = trim($component);
            $resolvedEvents = $this->normalizeCloseDispatches($events);

            if ($resolvedEvents !== []) {
                $normalized[$component] = $resolvedEvents;
            }
        }

        return $normalized;
    }

    public function render()
    {
        return view('corepine-modal::livewire.modal-host');
    }
}
