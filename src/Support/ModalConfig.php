<?php

namespace Corepine\Modal\Support;

use Corepine\Modal\Actions\Action as ModalAction;
use Corepine\Modal\Enums\ModalType;
use Corepine\Support\Colors\Color as SupportColor;
use Corepine\Support\Colors\ColorManager as SupportColorManager;

class ModalConfig
{
    /**
     * @var array<string, array<int, string>>
     */
    private const DEFAULT_LISTEN_EVENTS = [
        'open' => ['openModal', 'corepine-modal.open'],
        'open_sheet' => ['openBottomSheet', 'corepine-modal.open-sheet'],
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
     * Built-in size presets. Keeping them in src ensures Tailwind source
     * scanning can discover these utility classes out of the box.
     *
     * @var array<string, string>
     */
    private const DEFAULT_SIZES = [
        'default' => 'max-w-lg sm:max-w-full',
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

    private const DEFAULT_SIZE_CLASSES = 'max-w-lg sm:max-w-full';

    private const DEFAULT_MODAL_POSITION = 'center';

    private const DEFAULT_DRAWER_POSITION = 'right';

    private const DEFAULT_SHEET_POSITION = 'bottom';

    private const DEFAULT_MODAL_TYPE = ModalType::Modal->value;

    /**
     * @var array<int, string>
     */
    private const MODAL_POSITIONS = ['center', 'top', 'bottom', 'left', 'right'];

    /**
     * @var array<int, string>
     */
    private const DRAWER_POSITIONS = ['left', 'right'];

    /**
     * @var array<int, string>
     */
    private const SHEET_POSITIONS = ['bottom'];

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
        $defaults = $this->normalizeLegacyModalAttributeKeys($this->defaultModalAttributes());
        $componentAttributes = $this->normalizeLegacyModalAttributeKeys($componentAttributes);
        $runtimeAttributes = $this->normalizeLegacyModalAttributeKeys($runtimeAttributes);

        $merged = array_replace(
            $defaults,
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
            return self::DEFAULT_SIZES;
        }

        $normalized = self::DEFAULT_SIZES;

        foreach ($configured as $key => $value) {
            if (! is_string($key) || ! is_string($value) || $value === '') {
                continue;
            }

            $normalized[$key] = $value;
        }

        if ($normalized === []) {
            return self::DEFAULT_SIZES;
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
        return $this->modalType($attributes) === ModalType::Drawer->value;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function isSheet(array $attributes): bool
    {
        return $this->modalType($attributes) === ModalType::Sheet->value;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function modalType(array $attributes): string
    {
        $type = $attributes['type'] ?? null;

        if ($type instanceof ModalType) {
            return $type->value;
        }

        if (is_string($type)) {
            $normalized = strtolower(trim($type));
            $resolved = ModalType::tryFrom($normalized);

            if ($resolved !== null) {
                return $resolved->value;
            }
        }

        if ($this->normalizeBoolean($attributes['drawer'] ?? false)) {
            return ModalType::Drawer->value;
        }

        if ($this->normalizeBoolean($attributes['sheet'] ?? false)) {
            return ModalType::Sheet->value;
        }

        return self::DEFAULT_MODAL_TYPE;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function isIsolated(array $attributes): bool
    {
        return $this->normalizeBoolean($attributes['isolate'] ?? ($attributes['isolated'] ?? false));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function modalPosition(array $attributes): string
    {
        $type = $this->modalType($attributes);
        $position = $attributes['position'] ?? null;

        if (! is_string($position) || $position === '') {
            return match ($type) {
                ModalType::Drawer->value => self::DEFAULT_DRAWER_POSITION,
                ModalType::Sheet->value => self::DEFAULT_SHEET_POSITION,
                default => self::DEFAULT_MODAL_POSITION,
            };
        }

        $normalized = strtolower(trim($position));

        if ($type === ModalType::Drawer->value) {
            return in_array($normalized, self::DRAWER_POSITIONS, true)
                ? $normalized
                : self::DEFAULT_DRAWER_POSITION;
        }

        if ($type === ModalType::Sheet->value) {
            return in_array($normalized, self::SHEET_POSITIONS, true)
                ? $normalized
                : self::DEFAULT_SHEET_POSITION;
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

        if ($this->isSheet($attributes)) {
            return 'cp-modal-panel-wrap absolute inset-x-0 bottom-0 flex w-full items-end justify-center p-0';
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

        if ($this->isSheet($attributes)) {
            return [
                'enter' => 'duration-250 ease-out',
                'enterStart' => 'opacity-0 translate-y-full',
                'enterEnd' => 'opacity-100 translate-y-0',
                'leave' => 'duration-200 ease-in',
                'leaveStart' => 'opacity-100 translate-y-0',
                'leaveEnd' => 'opacity-0 translate-y-full',
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
    public function usesLayout(array $attributes): bool
    {
        if ($this->normalizeBoolean($attributes['plain'] ?? false, false)) {
            return false;
        }

        $defaults = $this->defaultModalAttributes();
        $layout = $attributes['layout'] ?? ($defaults['layout'] ?? true);

        return $this->normalizeBoolean($layout, true);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function layoutTitle(array $attributes): ?string
    {
        $title = $attributes['title'] ?? null;

        if (! is_string($title) || trim($title) === '') {
            return null;
        }

        return $title;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function layoutDescription(array $attributes): ?string
    {
        $description = $attributes['description'] ?? null;

        if (! is_string($description) || trim($description) === '') {
            return null;
        }

        return $description;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function layoutShowClose(array $attributes): bool
    {
        $defaults = $this->defaultModalAttributes();
        $showClose = $attributes['showClose'] ?? ($defaults['showClose'] ?? true);

        return $this->normalizeBoolean($showClose, true);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<int, array<string, mixed>>
     */
    public function layoutFooterActions(array $attributes): array
    {
        $actions = $attributes['footerActions'] ?? [];

        if ($actions instanceof \Traversable) {
            $actions = iterator_to_array($actions);
        }

        if (! is_array($actions)) {
            return [];
        }

        $normalized = [];

        foreach ($actions as $action) {
            if ($action instanceof ModalAction) {
                $action = $action->toArray();
            }

            if (is_string($action) && trim($action) !== '') {
                $method = trim($action);
                $presentation = $this->normalizeFooterActionPresentation([
                    'class' => '',
                    'attributes' => [],
                ], 'method');

                $normalized[] = [
                    'type' => 'method',
                    'label' => ucwords(str_replace(['-', '_'], ' ', $method)),
                    'method' => $method,
                    'params' => [],
                    'class' => $presentation['class'],
                    'style' => $presentation['style'],
                    'disabled' => $presentation['disabled'],
                    'outline' => $presentation['outline'],
                    'attributes' => $presentation['attributes'],
                    'buttonType' => 'button',
                ];

                continue;
            }

            if (! is_array($action)) {
                continue;
            }

            $label = is_string($action['label'] ?? null) && trim((string) $action['label']) !== ''
                ? trim((string) $action['label'])
                : null;
            $type = is_string($action['type'] ?? null) ? strtolower(trim((string) $action['type'])) : null;

            if (is_null($type) && $this->normalizeBoolean($action['close'] ?? false, false)) {
                $type = 'close';
            }

            if ($type === 'close') {
                $presentation = $this->normalizeFooterActionPresentation($action, 'close');

                $normalized[] = [
                    'type' => 'close',
                    'label' => $label ?? 'Close',
                    'class' => $presentation['class'],
                    'style' => $presentation['style'],
                    'disabled' => $presentation['disabled'],
                    'outline' => $presentation['outline'],
                    'attributes' => $presentation['attributes'],
                    'count' => max(1, is_numeric($action['count'] ?? null) ? (int) $action['count'] : 1),
                    'destroy' => $this->normalizeBoolean($action['destroy'] ?? true, true),
                    'force' => $this->normalizeBoolean($action['force'] ?? false, false),
                ];

                continue;
            }

            $method = is_string($action['method'] ?? null) ? trim((string) $action['method']) : '';

            if ($method === '') {
                continue;
            }

            $params = $action['params'] ?? [];

            if (! is_array($params)) {
                $params = [$params];
            }

            $buttonType = is_string($action['buttonType'] ?? null)
                ? strtolower(trim((string) $action['buttonType']))
                : 'button';

            if (! in_array($buttonType, ['button', 'submit', 'reset'], true)) {
                $buttonType = 'button';
            }

            $presentation = $this->normalizeFooterActionPresentation($action, 'method');

            $normalized[] = [
                'type' => 'method',
                'label' => $label ?? ucwords(str_replace(['-', '_'], ' ', $method)),
                'method' => $method,
                'params' => $params,
                'class' => $presentation['class'],
                'style' => $presentation['style'],
                'disabled' => $presentation['disabled'],
                'outline' => $presentation['outline'],
                'attributes' => $presentation['attributes'],
                'buttonType' => $buttonType,
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $action
     * @return array{class: string, style: string, disabled: bool, outline: bool, attributes: array<string, mixed>}
     */
    private function normalizeFooterActionPresentation(array $action, string $type): array
    {
        $class = is_string($action['class'] ?? null) ? trim((string) $action['class']) : '';
        $disabled = $this->normalizeBoolean($action['disabled'] ?? false, false);
        $attributes = $this->normalizeFooterActionAttributes($action['attributes'] ?? []);
        $attributeClass = is_string($attributes['class'] ?? null) ? trim((string) $attributes['class']) : '';
        $attributeStyle = is_string($attributes['style'] ?? null) ? trim((string) $attributes['style']) : '';
        unset($attributes['class'], $attributes['style'], $attributes['disabled']);

        if ($attributeClass !== '') {
            $class = trim(implode(' ', array_filter([$class, $attributeClass])));
        }

        $hasColor = array_key_exists('color', $action)
            && (
                (is_string($action['color']) && trim((string) $action['color']) !== '')
                || is_array($action['color'])
            );
        $hasOutline = array_key_exists('outline', $action) && ! is_null($action['outline']);
        $outlineDefault = $type === 'close';
        $outline = $hasOutline
            ? $this->normalizeBoolean($action['outline'], $outlineDefault)
            : $outlineDefault;
        $usesPresetStyling = $class === '' || $hasColor || $hasOutline;
        $style = '';

        if ($usesPresetStyling) {
            $palette = $this->resolveFooterActionPalette($action['color'] ?? ($type === 'close' ? 'gray' : 'primary'));
            $class = trim(implode(' ', array_filter([
                'cp-modal-action',
                $outline ? 'cp-modal-action-outline' : 'cp-modal-action-solid',
                $class,
            ])));
            $style = $this->footerActionStyle($palette, $outline);
        }

        if ($disabled) {
            $class = trim(implode(' ', array_filter([
                $class,
                'cp-modal-action-disabled',
            ])));
        }

        if ($attributeStyle !== '') {
            $style = trim(implode('; ', array_filter([$style, $attributeStyle])));
        }

        return [
            'class' => $class,
            'style' => $style,
            'disabled' => $disabled,
            'outline' => $outline,
            'attributes' => $attributes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeFooterActionAttributes(mixed $attributes): array
    {
        if ($attributes instanceof \Traversable) {
            $attributes = iterator_to_array($attributes);
        }

        if (! is_array($attributes)) {
            return [];
        }

        $normalized = [];

        foreach ($attributes as $key => $value) {
            if (! is_string($key) || trim($key) === '') {
                continue;
            }

            if (is_null($value) || $value === false) {
                continue;
            }

            if ($value === true) {
                $normalized[$key] = true;

                continue;
            }

            if (is_scalar($value) || $value instanceof \Stringable) {
                $normalized[$key] = (string) $value;
            }
        }

        return $normalized;
    }

    /**
     * @return array<int|string, string>|null
     */
    private function resolveFooterActionPalette(mixed $color): ?array
    {
        if (is_string($color)) {
            $color = trim($color);

            if ($color === '') {
                return null;
            }

            $palette = app()->bound(SupportColorManager::class)
                ? app(SupportColorManager::class)->palette($color)
                : null;

            return is_array($palette) ? $palette : SupportColor::palette($color);
        }

        if (! is_array($color)) {
            return null;
        }

        $normalized = [];

        foreach ($color as $shade => $value) {
            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);

            if ($value === '') {
                continue;
            }

            $normalized[is_int($shade) || ! ctype_digit((string) $shade) ? $shade : (int) $shade] = $value;
        }

        if ($normalized === []) {
            return null;
        }

        ksort($normalized);

        return $normalized;
    }

    /**
     * @param  array<int|string, string>|null  $palette
     */
    private function footerActionStyle(?array $palette, bool $outline): string
    {
        if ($palette === null) {
            return '';
        }

        $variables = $outline
            ? [
                '--cp-action-bg: transparent',
                '--cp-action-bg-hover: ' . ($this->paletteShade($palette, 50) ?? 'transparent'),
                '--cp-action-border: ' . ($this->paletteShade($palette, 200) ?? 'currentColor'),
                '--cp-action-border-hover: ' . ($this->paletteShade($palette, 300) ?? 'currentColor'),
                '--cp-action-text: ' . ($this->paletteShade($palette, 700) ?? 'currentColor'),
                '--cp-action-text-hover: ' . ($this->paletteShade($palette, 800) ?? 'currentColor'),
                '--cp-action-dark-bg: transparent',
                '--cp-action-dark-bg-hover: ' . ($this->paletteShade($palette, 950) ?? 'transparent'),
                '--cp-action-dark-border: ' . ($this->paletteShade($palette, 700) ?? 'currentColor'),
                '--cp-action-dark-border-hover: ' . ($this->paletteShade($palette, 600) ?? 'currentColor'),
                '--cp-action-dark-text: ' . ($this->paletteShade($palette, 200) ?? '#e4e4e7'),
                '--cp-action-dark-text-hover: ' . ($this->paletteShade($palette, 100) ?? '#f4f4f5'),
            ]
            : [
                '--cp-action-bg: ' . ($this->paletteShade($palette, 500) ?? '#18181b'),
                '--cp-action-bg-hover: ' . ($this->paletteShade($palette, 600) ?? '#27272a'),
                '--cp-action-border: ' . ($this->paletteShade($palette, 500) ?? '#18181b'),
                '--cp-action-border-hover: ' . ($this->paletteShade($palette, 600) ?? '#27272a'),
                '--cp-action-text: ' . $this->footerActionSolidTextColor($palette),
                '--cp-action-text-hover: ' . $this->footerActionSolidTextColor($palette),
                '--cp-action-dark-bg: ' . ($this->paletteShade($palette, 500) ?? '#27272a'),
                '--cp-action-dark-bg-hover: ' . ($this->paletteShade($palette, 400) ?? '#3f3f46'),
                '--cp-action-dark-border: ' . ($this->paletteShade($palette, 500) ?? '#27272a'),
                '--cp-action-dark-border-hover: ' . ($this->paletteShade($palette, 400) ?? '#3f3f46'),
                '--cp-action-dark-text: ' . $this->footerActionSolidTextColor($palette),
                '--cp-action-dark-text-hover: ' . $this->footerActionSolidTextColor($palette),
            ];

        return implode('; ', array_filter($variables));
    }

    /**
     * @param  array<int|string, string>  $palette
     */
    private function footerActionSolidTextColor(array $palette): string
    {
        $baseColor = $this->paletteShade($palette, 500) ?? '';

        return $this->isLightFooterActionColor($baseColor) ? '#18181b' : '#ffffff';
    }

    /**
     * @param  array<int|string, string>  $palette
     */
    private function paletteShade(array $palette, int $shade): ?string
    {
        return $palette[$shade] ?? $palette[500] ?? null;
    }

    private function isLightFooterActionColor(string $color): bool
    {
        if (preg_match('/^oklch\(\s*([0-9.]+)/i', $color, $matches) === 1) {
            return (float) ($matches[1] ?? 0) >= 0.72;
        }

        if (preg_match('/^#([0-9a-f]{6})$/i', $color, $matches) === 1) {
            $hex = $matches[1];
            $red = hexdec(substr($hex, 0, 2));
            $green = hexdec(substr($hex, 2, 2));
            $blue = hexdec(substr($hex, 4, 2));

            return ((0.299 * $red) + (0.587 * $green) + (0.114 * $blue)) / 255 >= 0.65;
        }

        return false;
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
        $attributes['type'] = $this->modalType($attributes);
        $attributes['drawer'] = $attributes['type'] === ModalType::Drawer->value;
        $attributes['sheet'] = $attributes['type'] === ModalType::Sheet->value;
        $attributes['isolate'] = $this->isIsolated($attributes);
        $attributes['layout'] = $this->usesLayout($attributes);
        $attributes['showClose'] = $this->layoutShowClose($attributes);
        $attributes['footerActions'] = $this->layoutFooterActions($attributes);
        unset($attributes['isolated']);
        unset($attributes['plain']);
        $attributes['position'] = $this->modalPosition($attributes);

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function normalizeLegacyModalAttributeKeys(array $attributes): array
    {
        if (! array_key_exists('isolate', $attributes) && array_key_exists('isolated', $attributes)) {
            $attributes['isolate'] = $attributes['isolated'];
        }

        if (! array_key_exists('layout', $attributes) && array_key_exists('plain', $attributes)) {
            $attributes['layout'] = ! $this->normalizeBoolean($attributes['plain'] ?? false, false);
        }

        if (! array_key_exists('type', $attributes)) {
            if ($this->normalizeBoolean($attributes['drawer'] ?? false)) {
                $attributes['type'] = ModalType::Drawer->value;
            } elseif ($this->normalizeBoolean($attributes['sheet'] ?? false)) {
                $attributes['type'] = ModalType::Sheet->value;
            }
        }

        return $attributes;
    }

    private function normalizeBoolean(mixed $value, bool $fallback = false): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return match (strtolower(trim($value))) {
                '1', 'true', 'yes', 'on' => true,
                '0', 'false', 'no', 'off' => false,
                default => $fallback,
            };
        }

        if (is_int($value) || is_float($value)) {
            if ((float) $value === 1.0) {
                return true;
            }

            if ((float) $value === 0.0) {
                return false;
            }
        }

        return $fallback;
    }
}
