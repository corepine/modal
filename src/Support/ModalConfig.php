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

    private const DEFAULT_SIZE_CLASSES = 'max-w-lg sm:max-w-full';

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
     * @return array<string, string>
     */
    public function sizes(): array
    {
        $configured = config('corepine-modal.sizes', []);

        if (! is_array($configured)) {
            return ['default' => self::DEFAULT_SIZE_CLASSES];
        }

        $normalized = [];

        foreach ($configured as $key => $value) {
            if (! is_string($key) || ! is_string($value) || $value === '') {
                continue;
            }

            $normalized[$key] = $value;
        }

        if ($normalized === []) {
            return ['default' => self::DEFAULT_SIZE_CLASSES];
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function modalSizeClasses(array $attributes): string
    {
        $customClasses = $attributes['sizeClasses'] ?? $attributes['sizeClass'] ?? null;

        if (is_string($customClasses) && $customClasses !== '') {
            return $customClasses;
        }

        $sizeToken = $attributes['size']
            ?? $attributes['width']
            ?? $attributes['maxWidth']
            ?? $this->defaultModalAttributes()['size']
            ?? 'default';

        $sizes = $this->sizes();

        if (is_string($sizeToken) && isset($sizes[$sizeToken])) {
            return $sizes[$sizeToken];
        }

        if (is_string($sizeToken) && $sizeToken !== '') {
            return $sizeToken;
        }

        return $sizes['default'] ?? reset($sizes) ?: self::DEFAULT_SIZE_CLASSES;
    }
}
