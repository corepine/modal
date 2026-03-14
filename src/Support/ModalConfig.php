<?php

namespace Corepine\Modal\Support;

class ModalConfig
{
    /**
     * @var array<string, array<int, string>>
     */
    private const DEFAULT_LISTEN_EVENTS = [
        'open' => ['openModal', 'corepine-modal.open'],
        'close' => ['closeModal', 'corepine-modal.close'],
        'close_top' => ['closeTopModal', 'corepine-modal.close-top'],
        'close_all' => ['closeAllModals', 'corepine-modal.close-all'],
        'destroy' => ['destroyModal', 'corepine-modal.destroy'],
        'reset' => ['resetModal', 'corepine-modal.reset'],
    ];

    /**
     * @var array<string, string>
     */
    private const DEFAULT_DISPATCH_EVENTS = [
        'opened' => 'modalOpened',
        'closed' => 'modalClosed',
        'changed' => 'activeModalChanged',
        'all_closed' => 'allModalsClosed',
        'component_closed' => 'modalComponentClosed',
    ];

    /**
     * @var array<string, string>
     */
    private const DEFAULT_WIDTH_CLASSES = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
        '6xl' => 'max-w-6xl',
        '7xl' => 'max-w-7xl',
    ];

    public function hostComponent(): string
    {
        return (string) config('corepine-modal.host_component', 'corepine-modal');
    }

    /**
     * @param  string  $key
     * @return array<int, string>
     */
    public function listenEvents(string $key): array
    {
        $configured = config("corepine-modal.events.listen.$key");

        if (is_string($configured) && $configured !== '') {
            return [$configured];
        }

        if (is_array($configured) && $configured !== []) {
            return array_values(array_filter($configured, static fn (mixed $value): bool => is_string($value) && $value !== ''));
        }

        return self::DEFAULT_LISTEN_EVENTS[$key] ?? [];
    }

    public function listenEvent(string $key): string
    {
        return $this->listenEvents($key)[0] ?? '';
    }

    public function dispatchEvent(string $key): string
    {
        $configured = config("corepine-modal.events.dispatch.$key");

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return self::DEFAULT_DISPATCH_EVENTS[$key] ?? $key;
    }

    /**
     * @param  array<string, string>  $methodMap
     * @return array<string, string>
     */
    public function listenersMap(array $methodMap): array
    {
        $listeners = [];

        foreach ($methodMap as $key => $method) {
            foreach ($this->listenEvents($key) as $eventName) {
                $listeners[$eventName] = $method;
            }
        }

        return $listeners;
    }

    /**
     * @return array<string, mixed>
     */
    public function defaultModalAttributes(): array
    {
        $defaults = config('corepine-modal.defaults.attributes', []);

        return is_array($defaults) ? $defaults : [];
    }

    /**
     * @param  array<string, mixed>  $componentAttributes
     * @param  array<string, mixed>  $runtimeAttributes
     * @return array<string, mixed>
     */
    public function mergedModalAttributes(array $componentAttributes = [], array $runtimeAttributes = []): array
    {
        return array_replace(
            $this->defaultModalAttributes(),
            $componentAttributes,
            $runtimeAttributes
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function widthClass(array $attributes): string
    {
        $rawWidth = $attributes['width'] ?? $attributes['maxWidth'] ?? '2xl';

        if (is_string($rawWidth) && str_starts_with($rawWidth, 'max-w-')) {
            return $rawWidth;
        }

        $widthKey = is_string($rawWidth) ? $rawWidth : '2xl';
        $widthClasses = config('corepine-modal.width_classes', self::DEFAULT_WIDTH_CLASSES);

        if (! is_array($widthClasses)) {
            $widthClasses = self::DEFAULT_WIDTH_CLASSES;
        }

        $resolved = $widthClasses[$widthKey] ?? null;

        if (is_string($resolved) && $resolved !== '') {
            return $resolved;
        }

        return self::DEFAULT_WIDTH_CLASSES['2xl'];
    }
}
