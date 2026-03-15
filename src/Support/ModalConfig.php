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

    private const DEFAULT_MODAL_POSITION = 'center';

    private const DEFAULT_DRAWER_POSITION = 'right';

    /**
     * @var array<int, string>
     */
    private const MODAL_POSITIONS = ['center', 'top', 'bottom', 'left', 'right'];

    /**
     * @var array<int, string>
     */
    private const DRAWER_POSITIONS = ['left', 'right'];

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
        $merged = array_replace(
            $this->defaultModalAttributes(),
            $componentAttributes,
            $runtimeAttributes
        );

        return $this->normalizeModalAttributes($merged);
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
        $sizeToken = $attributes['size']
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

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function modalClass(array $attributes): string
    {
        $class = $attributes['class'] ?? null;

        if (is_string($class) && $class !== '') {
            return $class;
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function isDrawer(array $attributes): bool
    {
        return $this->normalizeBoolean($attributes['drawer'] ?? false);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function modalPosition(array $attributes): string
    {
        $isDrawer = $this->isDrawer($attributes);
        $position = $attributes['position'] ?? null;

        if (! is_string($position) || $position === '') {
            return $isDrawer ? self::DEFAULT_DRAWER_POSITION : self::DEFAULT_MODAL_POSITION;
        }

        $normalized = strtolower(trim($position));

        if ($isDrawer) {
            return in_array($normalized, self::DRAWER_POSITIONS, true)
                ? $normalized
                : self::DEFAULT_DRAWER_POSITION;
        }

        return in_array($normalized, self::MODAL_POSITIONS, true)
            ? $normalized
            : self::DEFAULT_MODAL_POSITION;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function modalPanelWrapClasses(array $attributes): string
    {
        $position = $this->modalPosition($attributes);

        if ($this->isDrawer($attributes)) {
            return $position === 'left'
                ? 'cp-modal-panel-wrap absolute inset-0 flex w-full items-stretch justify-start p-0'
                : 'cp-modal-panel-wrap absolute inset-0 flex w-full items-stretch justify-end p-0';
        }

        return match ($position) {
            'top' => 'cp-modal-panel-wrap absolute inset-0 flex w-full items-start justify-center p-4 sm:p-8',
            'bottom' => 'cp-modal-panel-wrap absolute inset-0 flex w-full items-end justify-center p-4 sm:p-8',
            'left' => 'cp-modal-panel-wrap absolute inset-0 flex w-full items-center justify-start p-4 sm:p-8',
            'right' => 'cp-modal-panel-wrap absolute inset-0 flex w-full items-center justify-end p-4 sm:p-8',
            default => 'cp-modal-panel-wrap absolute inset-0 flex w-full items-center justify-center p-4 sm:p-8',
        };
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, string>
     */
    public function modalTransitionClasses(array $attributes): array
    {
        if ($this->isDrawer($attributes)) {
            $position = $this->modalPosition($attributes);
            $offscreen = $position === 'left' ? '-translate-x-full' : 'translate-x-full';

            return [
                'enter' => 'duration-250 ease-out',
                'enterStart' => "opacity-0 {$offscreen}",
                'enterEnd' => 'opacity-100 translate-x-0',
                'leave' => 'duration-200 ease-in',
                'leaveStart' => 'opacity-100 translate-x-0',
                'leaveEnd' => "opacity-0 {$offscreen}",
            ];
        }

        return [
            'enter' => 'duration-200 ease-out',
            'enterStart' => 'opacity-0 translate-y-6 sm:scale-95',
            'enterEnd' => 'opacity-100 translate-y-0 sm:scale-100',
            'leave' => 'duration-150 ease-in',
            'leaveStart' => 'opacity-100 translate-y-0 sm:scale-100',
            'leaveEnd' => 'opacity-0 translate-y-4 sm:scale-95',
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function mergedModalClasses(array $attributes): string
    {
        $sizeClasses = $this->modalSizeClasses($attributes);
        $class = $this->modalClass($attributes);

        return trim(implode(' ', array_filter([$sizeClasses, $class])));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function normalizeModalAttributes(array $attributes): array
    {
        $attributes['drawer'] = $this->normalizeBoolean($attributes['drawer'] ?? false);
        $attributes['position'] = $this->modalPosition($attributes);

        return $attributes;
    }

    private function normalizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return match (strtolower(trim($value))) {
                '1', 'true', 'yes', 'on' => true,
                default => false,
            };
        }

        return false;
    }
}
