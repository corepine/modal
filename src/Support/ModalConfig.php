<?php

namespace Corepine\Modal\Support;

use Corepine\Modal\Actions\Action as ModalAction;
use Corepine\Modal\Enums\ModalType;
use Corepine\Support\Colors\Color as SupportColor;
use Corepine\Support\Colors\ColorManager as SupportColorManager;
use Corepine\Support\Enums\Alignment;
use Corepine\Support\Enums\Placement;

class ModalConfig
{
    /**
     * Built-in incoming event names.
     *
     * @var array<string, string>
     */
    private const DEFAULT_LISTEN_EVENTS = [
        'open' => 'modal.open',
        'open_sheet' => 'modal.open-sheet',
        'close' => 'modal.close',
        'close_top' => 'modal.close-top',
        'close_all' => 'modal.close-all',
        'destroy' => 'modal.destroy',
        'reset' => 'modal.reset',
        'toggle' => 'modal.toggle',
    ];

    /**
     * @var array<string, string>
     */
    private const DEFAULT_DISPATCH_EVENTS = [
        'opened' => 'modal.opened',
        'closed' => 'modal.closed',
        'changed' => 'modal.changed',
        'all_closed' => 'modal.all-closed',
        'component_closed' => 'modal.component-closed',
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

    private const DEFAULT_MODAL_PLACEMENT = 'center';

    private const DEFAULT_MODAL_ORIGIN = 'center';

    private const DEFAULT_DRAWER_PLACEMENT = 'right';

    private const DEFAULT_SHEET_PLACEMENT = 'bottom';

    private const DEFAULT_MODAL_TYPE = ModalType::Modal->value;

    /**
     * @var array<int, string>
     */
    private const MODAL_PLACEMENTS = ['center', 'top', 'bottom', 'left', 'right'];

    /**
     * @var array<int, string>
     */
    private const DRAWER_PLACEMENTS = ['left', 'right'];

    /**
     * @var array<int, string>
     */
    private const SHEET_PLACEMENTS = ['bottom'];

    /**
     * @param  string  $key
     * @return array<int, string>
     */
    public function listenEvents(string $key): array
    {
        $eventName = $this->listenEvent($key);

        return $eventName !== '' ? [$eventName] : [];
    }

    public function listenEvent(string $key): string
    {
        $configured = config("corepine-modal.events.listen.$key");

        if (is_string($configured) && trim($configured) !== '') {
            return trim($configured);
        }

        return self::DEFAULT_LISTEN_EVENTS[$key] ?? '';
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
        $resolvedType = $this->normalizeModalTypeValue($attributes['type'] ?? null);

        if ($resolvedType !== null) {
            return $resolvedType;
        }

        if ($this->normalizeBoolean($attributes['drawer'] ?? false)) {
            return ModalType::Drawer->value;
        }

        if ($this->normalizeBoolean($attributes['sheet'] ?? ($attributes['bottomSheet'] ?? false))) {
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
    public function modalPlacement(array $attributes): string
    {
        $type = $this->modalType($attributes);

        if ($type === ModalType::Sheet->value) {
            return self::DEFAULT_SHEET_PLACEMENT;
        }

        $placement = Placement::fromValue($attributes['placement'] ?? null);

        if ($placement === null) {
            return match ($type) {
                ModalType::Drawer->value => self::DEFAULT_DRAWER_PLACEMENT,
                default => self::DEFAULT_MODAL_PLACEMENT,
            };
        }

        if ($type === ModalType::Drawer->value) {
            return in_array($placement->value, self::DRAWER_PLACEMENTS, true)
                ? $placement->value
                : self::DEFAULT_DRAWER_PLACEMENT;
        }

        return in_array($placement->value, self::MODAL_PLACEMENTS, true)
            ? $placement->value
            : self::DEFAULT_MODAL_PLACEMENT;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function modalOrigin(array $attributes): string
    {
        $type = $this->modalType($attributes);

        if ($type === ModalType::Sheet->value) {
            return self::DEFAULT_SHEET_PLACEMENT;
        }

        if ($type === ModalType::Drawer->value) {
            return $this->modalPlacement($attributes);
        }

        $origin = Placement::fromValue($attributes['origin'] ?? null);

        if ($origin !== null) {
            return $origin->value;
        }

        $placement = $this->modalPlacement($attributes);

        return in_array($placement, self::MODAL_PLACEMENTS, true)
            ? $placement
            : self::DEFAULT_MODAL_ORIGIN;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function modalOriginClass(array $attributes): string
    {
        return Placement::normalize($this->modalOrigin($attributes))->originClass();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function modalPanelWrapClasses(array $attributes): string
    {
        $placement = $this->modalPlacement($attributes);

        if ($this->isDrawer($attributes)) {
            return $placement === 'left'
                ? 'absolute inset-0 flex w-full items-stretch justify-start p-0'
                : 'absolute inset-0 flex w-full items-stretch justify-end p-0';
        }

        if ($this->isSheet($attributes)) {
            return 'absolute inset-x-0 bottom-0 flex w-full items-end justify-center p-0';
        }

        return match ($placement) {
            'top' => 'absolute inset-0 flex w-full items-start justify-center p-4 sm:p-8',
            'bottom' => 'absolute inset-0 flex w-full items-end justify-center p-4 sm:p-8',
            'left' => 'absolute inset-0 flex w-full items-center justify-start p-4 sm:p-8',
            'right' => 'absolute inset-0 flex w-full items-center justify-end p-4 sm:p-8',
            default => 'absolute inset-0 flex w-full items-center justify-center p-4 sm:p-8',
        };
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, string>
     */
    public function modalTransitionClasses(array $attributes): array
    {
        if ($this->isDrawer($attributes)) {
            $placement = $this->modalPlacement($attributes);
            $offscreen = $placement === 'left' ? '-translate-x-full' : 'translate-x-full';

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

        return match ($this->modalOrigin($attributes)) {
            'top' => [
                'enter' => 'duration-200 ease-out',
                'enterStart' => 'opacity-0 -translate-y-6 sm:scale-95',
                'enterEnd' => 'opacity-100 translate-y-0 sm:scale-100',
                'leave' => 'duration-150 ease-in',
                'leaveStart' => 'opacity-100 translate-y-0 sm:scale-100',
                'leaveEnd' => 'opacity-0 -translate-y-4 sm:scale-95',
            ],
            'bottom' => [
                'enter' => 'duration-200 ease-out',
                'enterStart' => 'opacity-0 translate-y-6 sm:scale-95',
                'enterEnd' => 'opacity-100 translate-y-0 sm:scale-100',
                'leave' => 'duration-150 ease-in',
                'leaveStart' => 'opacity-100 translate-y-0 sm:scale-100',
                'leaveEnd' => 'opacity-0 translate-y-4 sm:scale-95',
            ],
            'left' => [
                'enter' => 'duration-200 ease-out',
                'enterStart' => 'opacity-0 -translate-x-6 sm:scale-95',
                'enterEnd' => 'opacity-100 translate-x-0 sm:scale-100',
                'leave' => 'duration-150 ease-in',
                'leaveStart' => 'opacity-100 translate-x-0 sm:scale-100',
                'leaveEnd' => 'opacity-0 -translate-x-4 sm:scale-95',
            ],
            'right' => [
                'enter' => 'duration-200 ease-out',
                'enterStart' => 'opacity-0 translate-x-6 sm:scale-95',
                'enterEnd' => 'opacity-100 translate-x-0 sm:scale-100',
                'leave' => 'duration-150 ease-in',
                'leaveStart' => 'opacity-100 translate-x-0 sm:scale-100',
                'leaveEnd' => 'opacity-0 translate-x-4 sm:scale-95',
            ],
            default => [
                'enter' => 'duration-200 ease-out',
                'enterStart' => 'opacity-0 translate-y-6 sm:scale-95',
                'enterEnd' => 'opacity-100 translate-y-0 sm:scale-100',
                'leave' => 'duration-150 ease-in',
                'leaveStart' => 'opacity-100 translate-y-0 sm:scale-100',
                'leaveEnd' => 'opacity-0 translate-y-4 sm:scale-95',
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function usesLayout(array $attributes): bool
    {
        $normalizedShell = array_key_exists('shell', $attributes)
            ? $this->normalizeNullableBoolean($attributes['shell'])
            : null;

        if (! is_null($normalizedShell)) {
            return $normalizedShell;
        }

        $normalizedLayout = array_key_exists('layout', $attributes)
            ? $this->normalizeNullableBoolean($attributes['layout'])
            : null;

        if (! is_null($normalizedLayout)) {
            return $normalizedLayout;
        }

        $defaults = $this->defaultModalAttributes();
        $defaultShell = array_key_exists('shell', $defaults)
            ? $this->normalizeNullableBoolean($defaults['shell'])
            : null;

        if (! is_null($defaultShell)) {
            return $defaultShell;
        }

        $defaultLayout = array_key_exists('layout', $defaults)
            ? $this->normalizeNullableBoolean($defaults['layout'])
            : null;

        if (! is_null($defaultLayout)) {
            return $defaultLayout;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function layoutHeading(array $attributes): ?string
    {
        $heading = $attributes['heading'] ?? null;

        if (! is_string($heading) || trim($heading) === '') {
            return null;
        }

        return $heading;
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
        $showClose = $attributes['showClose'] ?? null;

        if (! is_null($showClose)) {
            return $this->normalizeBoolean($showClose, true);
        }

        return $this->layoutHeading($attributes) !== null
            || $this->layoutDescription($attributes) !== null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<int, array<string, mixed>>
     */
    public function layoutFooterActions(array $attributes): array
    {
        $actions = $attributes['actions'] ?? [];

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

            if (is_array($action) && ($action['visible'] ?? true) === false) {
                continue;
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
                    'visible' => $presentation['visible'],
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
            $dispatch = is_array($action['dispatch'] ?? null) ? $action['dispatch'] : [];
            $dispatchTo = is_array($action['dispatchTo'] ?? null) ? $action['dispatchTo'] : [];
            $target = is_string($action['target'] ?? null) ? trim((string) $action['target']) : '';
            $event = is_string($action['event'] ?? null) ? trim((string) $action['event']) : '';
            $payload = is_array($action['payload'] ?? null) ? $action['payload'] : [];
            $method = is_string($action['method'] ?? null) ? trim((string) $action['method']) : '';
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

            if (is_null($type) && $this->normalizeBoolean($action['close'] ?? false, false)) {
                $type = 'close';
            }

            if (is_null($type)) {
                if ($method !== '') {
                    $type = 'method';
                } elseif ($target !== '' && $event !== '') {
                    $type = 'dispatchto';
                } elseif ($event !== '') {
                    $type = 'dispatch';
                } else {
                    $type = 'button';
                }
            }

            if ($type === 'close') {
                $presentation = $this->normalizeFooterActionPresentation($action, 'close');

                $normalized[] = [
                    'type' => 'close',
                    'label' => $label ?? 'Close',
                    'class' => $presentation['class'],
                    'style' => $presentation['style'],
                    'disabled' => $presentation['disabled'],
                    'visible' => $presentation['visible'],
                    'outline' => $presentation['outline'],
                    'attributes' => $presentation['attributes'],
                    'layers' => max(1, is_numeric($action['layers'] ?? null) ? (int) $action['layers'] : 1),
                    'destroy' => $this->normalizeBoolean($action['destroy'] ?? true, true),
                    'closeAll' => $this->normalizeBoolean($action['closeAll'] ?? false, false),
                    'dispatch' => $dispatch,
                    'dispatchTo' => $dispatchTo,
                ];

                continue;
            }

            if ($type === 'dispatchto' || $type === 'dispatch_to' || $type === 'dispatch-to') {
                $presentation = $this->normalizeFooterActionPresentation($action, 'dispatchto');

                $normalized[] = [
                    'type' => 'dispatchTo',
                    'label' => $label ?? ucwords(str_replace(['-', '_', '.'], ' ', $event !== '' ? $event : 'action')),
                    'class' => $presentation['class'],
                    'style' => $presentation['style'],
                    'disabled' => $presentation['disabled'],
                    'visible' => $presentation['visible'],
                    'outline' => $presentation['outline'],
                    'attributes' => $presentation['attributes'],
                    'buttonType' => $buttonType,
                    'target' => $target,
                    'event' => $event,
                    'payload' => $payload,
                ];

                continue;
            }

            if ($type === 'dispatch') {
                $presentation = $this->normalizeFooterActionPresentation($action, 'dispatch');

                $normalized[] = [
                    'type' => 'dispatch',
                    'label' => $label ?? ucwords(str_replace(['-', '_', '.'], ' ', $event !== '' ? $event : 'action')),
                    'class' => $presentation['class'],
                    'style' => $presentation['style'],
                    'disabled' => $presentation['disabled'],
                    'visible' => $presentation['visible'],
                    'outline' => $presentation['outline'],
                    'attributes' => $presentation['attributes'],
                    'buttonType' => $buttonType,
                    'event' => $event,
                    'payload' => $payload,
                ];

                continue;
            }

            if ($type === 'button') {
                $presentation = $this->normalizeFooterActionPresentation($action, 'button');

                $normalized[] = [
                    'type' => 'button',
                    'label' => $label ?? 'Action',
                    'class' => $presentation['class'],
                    'style' => $presentation['style'],
                    'disabled' => $presentation['disabled'],
                    'visible' => $presentation['visible'],
                    'outline' => $presentation['outline'],
                    'attributes' => $presentation['attributes'],
                    'buttonType' => $buttonType,
                ];

                continue;
            }

            if ($type !== 'method' || $method === '') {
                continue;
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
                'visible' => $presentation['visible'],
                'outline' => $presentation['outline'],
                'attributes' => $presentation['attributes'],
                'buttonType' => $buttonType,
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function layoutFooterActionsAlignment(array $attributes): string
    {
        $defaults = $this->defaultModalAttributes();
        $alignment = $attributes['footerActionsAlignment']
            ?? ($attributes['footerActionsAlign'] ?? ($defaults['footerActionsAlignment'] ?? Alignment::Right));

        return $this->normalizeAlignmentValue($alignment) ?? Alignment::Right->value;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function layoutFooterActionsAlignmentClass(array $attributes): string
    {
        return match ($this->layoutFooterActionsAlignment($attributes)) {
            Alignment::Start->value => 'justify-start',
            Alignment::Center->value => 'justify-center',
            default => 'justify-end',
        };
    }

    /**
     * @param  array<string, mixed>  $action
     * @return array{class: string, style: string, disabled: bool, visible: bool, outline: bool, attributes: array<string, mixed>}
     */
    private function normalizeFooterActionPresentation(array $action, string $type): array
    {
        $class = is_string($action['class'] ?? null) ? trim((string) $action['class']) : '';
        $disabled = $this->normalizeBoolean($action['disabled'] ?? false, false);
        $visible = $this->normalizeBoolean($action['visible'] ?? true, true);
        $attributes = $this->normalizeFooterActionAttributes($action['attributes'] ?? []);
        $attributeClass = is_string($attributes['class'] ?? null) ? trim((string) $attributes['class']) : '';
        $attributeStyle = is_string($attributes['style'] ?? null) ? trim((string) $attributes['style']) : '';
        unset($attributes['class'], $attributes['style'], $attributes['disabled']);

        if (($action['resolved'] ?? false) === true) {
            if ($attributeClass !== '') {
                $class = trim(implode(' ', array_filter([$class, $attributeClass])));
            }

            if ($disabled) {
                $class = trim(implode(' ', array_filter([
                    $class,
                    ModalActionClasses::DISABLED,
                ])));
            }

            $style = is_string($action['style'] ?? null) ? trim((string) $action['style']) : '';

            if ($attributeStyle !== '') {
                $style = trim(implode('; ', array_filter([$style, $attributeStyle])));
            }

            return [
                'class' => $class,
                'style' => $style,
                'disabled' => $disabled,
                'visible' => $visible,
                'outline' => $this->normalizeBoolean($action['outline'] ?? false, false),
                'attributes' => $attributes,
            ];
        }

        if ($attributeClass !== '') {
            $class = trim(implode(' ', array_filter([$class, $attributeClass])));
        }

        $hasColor = array_key_exists('color', $action)
            && (
                (is_string($action['color']) && trim((string) $action['color']) !== '')
                || is_array($action['color'])
            );
        $hasOutline = array_key_exists('outline', $action) && ! is_null($action['outline']);
        $outlineDefault = $type === 'close' || ! $hasColor;
        $outline = $hasOutline
            ? $this->normalizeBoolean($action['outline'], $outlineDefault)
            : $outlineDefault;
        $usesPresetStyling = $class === '' || $hasColor || $hasOutline;
        $style = '';
        $paletteName = $this->resolveFooterActionPaletteName($action['color'] ?? ($outlineDefault ? 'gray' : 'primary'));

        $class = trim(implode(' ', array_filter([
            ModalActionClasses::BASE,
            $class,
        ])));

        if ($usesPresetStyling && $paletteName !== null) {
            $class = trim(implode(' ', array_filter([
                $class,
                $this->footerActionPaletteClasses($paletteName, $outline, $disabled),
            ])));
        }

        if ($disabled) {
            $class = trim(implode(' ', array_filter([
                $class,
                ModalActionClasses::DISABLED,
            ])));
        }

        if ($attributeStyle !== '') {
            $style = trim(implode('; ', array_filter([$style, $attributeStyle])));
        }

        return [
            'class' => $class,
            'style' => $style,
            'disabled' => $disabled,
            'visible' => $visible,
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
    private function resolveFooterActionPaletteName(mixed $color): ?string
    {
        if (is_string($color)) {
            $color = trim($color);

            if ($color === '') {
                return null;
            }

            $palette = app()->bound(SupportColorManager::class)
                ? app(SupportColorManager::class)->palette($color)
                : null;

            if (is_array($palette)) {
                return $this->matchFooterActionPaletteName($palette);
            }

            $builtin = SupportColor::palette($color);

            if (is_array($builtin)) {
                return $this->matchFooterActionPaletteName($builtin);
            }

            return $this->semanticFooterActionPaletteName($color);
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

        return $this->matchFooterActionPaletteName($normalized);
    }

    /**
     * Resolve semantic aliases for common action intent names.
     *
     * @return string|null
     */
    private function semanticFooterActionPaletteName(string $name): ?string
    {
        return match (strtolower(trim($name))) {
            'primary' => 'blue',
            'danger' => 'red',
            'success' => 'green',
            'warning' => 'amber',
            'info' => 'sky',
            'secondary' => 'zinc',
            default => null,
        };
    }

    /**
     * @param  array<int|string, string>  $palette
     */
    private function matchFooterActionPaletteName(array $palette): ?string
    {
        foreach (SupportColor::catalog() as $name => $builtInPalette) {
            if ($builtInPalette === $palette) {
                return $name;
            }
        }

        return null;
    }

    private function footerActionPaletteClasses(string $paletteName, bool $outline, bool $disabled): string
    {
        $name = strtolower(trim($paletteName));

        if ($disabled) {
            return match ($name) {
                'red' => $outline ? 'bg-transparent border-red-200 text-red-700 dark:bg-transparent dark:border-red-700 dark:text-red-200' : 'bg-red-600 border-red-600 text-white dark:bg-red-500 dark:border-red-500 dark:text-white',
                'green' => $outline ? 'bg-transparent border-green-200 text-green-700 dark:bg-transparent dark:border-green-700 dark:text-green-200' : 'bg-green-600 border-green-600 text-white dark:bg-green-500 dark:border-green-500 dark:text-white',
                'yellow' => $outline ? 'bg-transparent border-yellow-200 text-yellow-700 dark:bg-transparent dark:border-yellow-700 dark:text-yellow-200' : 'bg-yellow-600 border-yellow-600 text-white dark:bg-yellow-500 dark:border-yellow-500 dark:text-white',
                'sky' => $outline ? 'bg-transparent border-sky-200 text-sky-700 dark:bg-transparent dark:border-sky-700 dark:text-sky-200' : 'bg-sky-600 border-sky-600 text-white dark:bg-sky-500 dark:border-sky-500 dark:text-white',
                'gray' => $outline ? 'bg-transparent border-gray-200 text-gray-700 dark:bg-transparent dark:border-gray-700 dark:text-gray-200' : 'bg-gray-600 border-gray-600 text-white dark:bg-gray-500 dark:border-gray-500 dark:text-white',
                'zinc' => $outline ? 'bg-transparent border-zinc-200 text-zinc-700 dark:bg-transparent dark:border-zinc-700 dark:text-zinc-200' : 'bg-zinc-600 border-zinc-600 text-white dark:bg-zinc-500 dark:border-zinc-500 dark:text-white',
                'blue' => $outline ? 'bg-transparent border-blue-200 text-blue-700 dark:bg-transparent dark:border-blue-700 dark:text-blue-200' : 'bg-blue-600 border-blue-600 text-white dark:bg-blue-500 dark:border-blue-500 dark:text-white',
                'amber' => $outline ? 'bg-transparent border-amber-200 text-amber-700 dark:bg-transparent dark:border-amber-700 dark:text-amber-200' : 'bg-amber-600 border-amber-600 text-white dark:bg-amber-500 dark:border-amber-500 dark:text-white',
                'fuchsia' => $outline ? 'bg-transparent border-fuchsia-200 text-fuchsia-700 dark:bg-transparent dark:border-fuchsia-700 dark:text-fuchsia-200' : 'bg-fuchsia-600 border-fuchsia-600 text-white dark:bg-fuchsia-500 dark:border-fuchsia-500 dark:text-white',
                'purple' => $outline ? 'bg-transparent border-purple-200 text-purple-700 dark:bg-transparent dark:border-purple-700 dark:text-purple-200' : 'bg-purple-600 border-purple-600 text-white dark:bg-purple-500 dark:border-purple-500 dark:text-white',
                'pink' => $outline ? 'bg-transparent border-pink-200 text-pink-700 dark:bg-transparent dark:border-pink-700 dark:text-pink-200' : 'bg-pink-600 border-pink-600 text-white dark:bg-pink-500 dark:border-pink-500 dark:text-white',
                'rose' => $outline ? 'bg-transparent border-rose-200 text-rose-700 dark:bg-transparent dark:border-rose-700 dark:text-rose-200' : 'bg-rose-600 border-rose-600 text-white dark:bg-rose-500 dark:border-rose-500 dark:text-white',
                'indigo' => $outline ? 'bg-transparent border-indigo-200 text-indigo-700 dark:bg-transparent dark:border-indigo-700 dark:text-indigo-200' : 'bg-indigo-600 border-indigo-600 text-white dark:bg-indigo-500 dark:border-indigo-500 dark:text-white',
                'teal' => $outline ? 'bg-transparent border-teal-200 text-teal-700 dark:bg-transparent dark:border-teal-700 dark:text-teal-200' : 'bg-teal-600 border-teal-600 text-white dark:bg-teal-500 dark:border-teal-500 dark:text-white',
                'cyan' => $outline ? 'bg-transparent border-cyan-200 text-cyan-700 dark:bg-transparent dark:border-cyan-700 dark:text-cyan-200' : 'bg-cyan-600 border-cyan-600 text-white dark:bg-cyan-500 dark:border-cyan-500 dark:text-white',
                'emerald' => $outline ? 'bg-transparent border-emerald-200 text-emerald-700 dark:bg-transparent dark:border-emerald-700 dark:text-emerald-200' : 'bg-emerald-600 border-emerald-600 text-white dark:bg-emerald-500 dark:border-emerald-500 dark:text-white',
                default => '',
            };
        }

        return match ($name) {
            'red' => $outline ? 'bg-transparent border-red-200 text-red-700 hover:bg-red-50 hover:border-red-300 hover:text-red-800 dark:bg-transparent dark:border-red-700 dark:text-red-200 dark:hover:bg-red-950 dark:hover:border-red-600 dark:hover:text-red-100' : 'bg-red-600 border-red-600 text-white hover:bg-red-500 hover:border-red-500 dark:bg-red-500 dark:border-red-500 dark:text-white dark:hover:bg-red-600 dark:hover:border-red-600',
            'green' => $outline ? 'bg-transparent border-green-200 text-green-700 hover:bg-green-50 hover:border-green-300 hover:text-green-800 dark:bg-transparent dark:border-green-700 dark:text-green-200 dark:hover:bg-green-950 dark:hover:border-green-600 dark:hover:text-green-100' : 'bg-green-600 border-green-600 text-white hover:bg-green-500 hover:border-green-500 dark:bg-green-500 dark:border-green-500 dark:text-white dark:hover:bg-green-600 dark:hover:border-green-600',
            'yellow' => $outline ? 'bg-transparent border-yellow-200 text-yellow-700 hover:bg-yellow-50 hover:border-yellow-300 hover:text-yellow-800 dark:bg-transparent dark:border-yellow-700 dark:text-yellow-200 dark:hover:bg-yellow-950 dark:hover:border-yellow-600 dark:hover:text-yellow-100' : 'bg-yellow-600 border-yellow-600 text-white hover:bg-yellow-500 hover:border-yellow-500 dark:bg-yellow-500 dark:border-yellow-500 dark:text-white dark:hover:bg-yellow-600 dark:hover:border-yellow-600',
            'sky' => $outline ? 'bg-transparent border-sky-200 text-sky-700 hover:bg-sky-50 hover:border-sky-300 hover:text-sky-800 dark:bg-transparent dark:border-sky-700 dark:text-sky-200 dark:hover:bg-sky-950 dark:hover:border-sky-600 dark:hover:text-sky-100' : 'bg-sky-600 border-sky-600 text-white hover:bg-sky-500 hover:border-sky-500 dark:bg-sky-500 dark:border-sky-500 dark:text-white dark:hover:bg-sky-600 dark:hover:border-sky-600',
            'gray' => $outline ? 'bg-transparent border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 dark:bg-transparent dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-950 dark:hover:border-gray-600 dark:hover:text-gray-100' : 'bg-gray-600 border-gray-600 text-white hover:bg-gray-500 hover:border-gray-500 dark:bg-gray-500 dark:border-gray-500 dark:text-white dark:hover:bg-gray-600 dark:hover:border-gray-600',
            'zinc' => $outline ? 'bg-transparent border-zinc-200 text-zinc-700 hover:bg-zinc-50 hover:border-zinc-300 hover:text-zinc-800 dark:bg-transparent dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-950 dark:hover:border-zinc-600 dark:hover:text-zinc-100' : 'bg-zinc-600 border-zinc-600 text-white hover:bg-zinc-500 hover:border-zinc-500 dark:bg-zinc-500 dark:border-zinc-500 dark:text-white dark:hover:bg-zinc-600 dark:hover:border-zinc-600',
            'blue' => $outline ? 'bg-transparent border-blue-200 text-blue-700 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-800 dark:bg-transparent dark:border-blue-700 dark:text-blue-200 dark:hover:bg-blue-950 dark:hover:border-blue-600 dark:hover:text-blue-100' : 'bg-blue-600 border-blue-600 text-white hover:bg-blue-500 hover:border-blue-500 dark:bg-blue-500 dark:border-blue-500 dark:text-white dark:hover:bg-blue-600 dark:hover:border-blue-600',
            'amber' => $outline ? 'bg-transparent border-amber-200 text-amber-700 hover:bg-amber-50 hover:border-amber-300 hover:text-amber-800 dark:bg-transparent dark:border-amber-700 dark:text-amber-200 dark:hover:bg-amber-950 dark:hover:border-amber-600 dark:hover:text-amber-100' : 'bg-amber-600 border-amber-600 text-white hover:bg-amber-500 hover:border-amber-500 dark:bg-amber-500 dark:border-amber-500 dark:text-white dark:hover:bg-amber-600 dark:hover:border-amber-600',
            'fuchsia' => $outline ? 'bg-transparent border-fuchsia-200 text-fuchsia-700 hover:bg-fuchsia-50 hover:border-fuchsia-300 hover:text-fuchsia-800 dark:bg-transparent dark:border-fuchsia-700 dark:text-fuchsia-200 dark:hover:bg-fuchsia-950 dark:hover:border-fuchsia-600 dark:hover:text-fuchsia-100' : 'bg-fuchsia-600 border-fuchsia-600 text-white hover:bg-fuchsia-500 hover:border-fuchsia-500 dark:bg-fuchsia-500 dark:border-fuchsia-500 dark:text-white dark:hover:bg-fuchsia-600 dark:hover:border-fuchsia-600',
            'purple' => $outline ? 'bg-transparent border-purple-200 text-purple-700 hover:bg-purple-50 hover:border-purple-300 hover:text-purple-800 dark:bg-transparent dark:border-purple-700 dark:text-purple-200 dark:hover:bg-purple-950 dark:hover:border-purple-600 dark:hover:text-purple-100' : 'bg-purple-600 border-purple-600 text-white hover:bg-purple-500 hover:border-purple-500 dark:bg-purple-500 dark:border-purple-500 dark:text-white dark:hover:bg-purple-600 dark:hover:border-purple-600',
            'pink' => $outline ? 'bg-transparent border-pink-200 text-pink-700 hover:bg-pink-50 hover:border-pink-300 hover:text-pink-800 dark:bg-transparent dark:border-pink-700 dark:text-pink-200 dark:hover:bg-pink-950 dark:hover:border-pink-600 dark:hover:text-pink-100' : 'bg-pink-600 border-pink-600 text-white hover:bg-pink-500 hover:border-pink-500 dark:bg-pink-500 dark:border-pink-500 dark:text-white dark:hover:bg-pink-600 dark:hover:border-pink-600',
            'rose' => $outline ? 'bg-transparent border-rose-200 text-rose-700 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-800 dark:bg-transparent dark:border-rose-700 dark:text-rose-200 dark:hover:bg-rose-950 dark:hover:border-rose-600 dark:hover:text-rose-100' : 'bg-rose-600 border-rose-600 text-white hover:bg-rose-500 hover:border-rose-500 dark:bg-rose-500 dark:border-rose-500 dark:text-white dark:hover:bg-rose-600 dark:hover:border-rose-600',
            'indigo' => $outline ? 'bg-transparent border-indigo-200 text-indigo-700 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-800 dark:bg-transparent dark:border-indigo-700 dark:text-indigo-200 dark:hover:bg-indigo-950 dark:hover:border-indigo-600 dark:hover:text-indigo-100' : 'bg-indigo-600 border-indigo-600 text-white hover:bg-indigo-500 hover:border-indigo-500 dark:bg-indigo-500 dark:border-indigo-500 dark:text-white dark:hover:bg-indigo-600 dark:hover:border-indigo-600',
            'teal' => $outline ? 'bg-transparent border-teal-200 text-teal-700 hover:bg-teal-50 hover:border-teal-300 hover:text-teal-800 dark:bg-transparent dark:border-teal-700 dark:text-teal-200 dark:hover:bg-teal-950 dark:hover:border-teal-600 dark:hover:text-teal-100' : 'bg-teal-600 border-teal-600 text-white hover:bg-teal-500 hover:border-teal-500 dark:bg-teal-500 dark:border-teal-500 dark:text-white dark:hover:bg-teal-600 dark:hover:border-teal-600',
            'cyan' => $outline ? 'bg-transparent border-cyan-200 text-cyan-700 hover:bg-cyan-50 hover:border-cyan-300 hover:text-cyan-800 dark:bg-transparent dark:border-cyan-700 dark:text-cyan-200 dark:hover:bg-cyan-950 dark:hover:border-cyan-600 dark:hover:text-cyan-100' : 'bg-cyan-600 border-cyan-600 text-white hover:bg-cyan-500 hover:border-cyan-500 dark:bg-cyan-500 dark:border-cyan-500 dark:text-white dark:hover:bg-cyan-600 dark:hover:border-cyan-600',
            'emerald' => $outline ? 'bg-transparent border-emerald-200 text-emerald-700 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-800 dark:bg-transparent dark:border-emerald-700 dark:text-emerald-200 dark:hover:bg-emerald-950 dark:hover:border-emerald-600 dark:hover:text-emerald-100' : 'bg-emerald-600 border-emerald-600 text-white hover:bg-emerald-500 hover:border-emerald-500 dark:bg-emerald-500 dark:border-emerald-500 dark:text-white dark:hover:bg-emerald-600 dark:hover:border-emerald-600',
            default => '',
        };
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
        $attributes['dismissible'] = $this->normalizeBoolean(
            $attributes['dismissible'] ?? true,
            true
        );
        $attributes['closeAllOnEscape'] = $this->normalizeBoolean(
            $attributes['closeAllOnEscape'] ?? false,
            false
        );
        $attributes['draggable'] = $attributes['type'] === ModalType::Sheet->value
            ? $this->normalizeBoolean($attributes['draggable'] ?? ($attributes['enableDrag'] ?? true), true)
            : $this->normalizeBoolean($attributes['draggable'] ?? ($attributes['enableDrag'] ?? false), false);
        $attributes['showDragHandle'] = $attributes['type'] === ModalType::Sheet->value
            ? $this->normalizeBoolean($attributes['showDragHandle'] ?? $attributes['draggable'], $attributes['draggable'])
            : $this->normalizeBoolean($attributes['showDragHandle'] ?? false, false);
        $attributes['isolate'] = $this->isIsolated($attributes);
        $attributes['shell'] = $this->usesLayout($attributes);
        $attributes['showClose'] = $this->layoutShowClose($attributes);
        $attributes['footerActionsAlignment'] = $this->layoutFooterActionsAlignment($attributes);
        $attributes['actions'] = $this->layoutFooterActions($attributes);
        $attributes['dispatch'] = is_array($attributes['dispatch'] ?? null) ? $attributes['dispatch'] : [];
        $attributes['dispatchTo'] = is_array($attributes['dispatchTo'] ?? null) ? $attributes['dispatchTo'] : [];
        unset($attributes['isolated']);
        unset($attributes['bottomSheet']);
        unset($attributes['closeOnClickAway']);
        unset($attributes['closeOnEscapeIsForceful']);
        unset($attributes['enableDrag']);
        unset($attributes['footerActionsAlign']);
        unset($attributes['layout']);
        $attributes['placement'] = $this->modalPlacement($attributes);
        $attributes['origin'] = $this->modalOrigin($attributes);

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

        if (! array_key_exists('draggable', $attributes) && array_key_exists('enableDrag', $attributes)) {
            $attributes['draggable'] = $attributes['enableDrag'];
        }

        if (! array_key_exists('enableDrag', $attributes) && array_key_exists('draggable', $attributes)) {
            $attributes['enableDrag'] = $attributes['draggable'];
        }

        if (! array_key_exists('sheet', $attributes) && array_key_exists('bottomSheet', $attributes)) {
            $attributes['sheet'] = $attributes['bottomSheet'];
        }

        if (! array_key_exists('bottomSheet', $attributes) && array_key_exists('sheet', $attributes)) {
            $attributes['bottomSheet'] = $attributes['sheet'];
        }

        if (! array_key_exists('shell', $attributes) && array_key_exists('layout', $attributes)) {
            $attributes['shell'] = $attributes['layout'];
        }

        if (! array_key_exists('layout', $attributes) && array_key_exists('shell', $attributes)) {
            $attributes['layout'] = $attributes['shell'];
        }

        if (! array_key_exists('footerActionsAlignment', $attributes) && array_key_exists('footerActionsAlign', $attributes)) {
            $attributes['footerActionsAlignment'] = $attributes['footerActionsAlign'];
        }

        if (array_key_exists('type', $attributes)) {
            $normalizedType = $this->normalizeModalTypeValue($attributes['type']);

            if ($normalizedType !== null) {
                $attributes['type'] = $normalizedType;
            }
        }

        if (! array_key_exists('type', $attributes)) {
            if ($this->normalizeBoolean($attributes['drawer'] ?? false)) {
                $attributes['type'] = ModalType::Drawer->value;
            } elseif ($this->normalizeBoolean($attributes['sheet'] ?? ($attributes['bottomSheet'] ?? false))) {
                $attributes['type'] = ModalType::Sheet->value;
            }
        }

        return $attributes;
    }

    private function normalizeModalTypeValue(mixed $type): ?string
    {
        if ($type instanceof ModalType) {
            return $type->value;
        }

        if (! is_string($type)) {
            return null;
        }

        $normalized = strtolower(trim($type));

        if ($normalized === '') {
            return null;
        }

        if (in_array($normalized, ['bottomsheet', 'bottom-sheet', 'bottom_sheet'], true)) {
            return ModalType::Sheet->value;
        }

        return ModalType::tryFrom($normalized)?->value;
    }

    private function normalizeAlignmentValue(mixed $alignment): ?string
    {
        if ($alignment instanceof Alignment) {
            return $alignment->value;
        }

        if (! is_string($alignment)) {
            return null;
        }

        return match (strtolower(trim($alignment))) {
            'start', 'left' => Alignment::Start->value,
            'center', 'middle' => Alignment::Center->value,
            'end', 'right' => Alignment::Right->value,
            default => null,
        };
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

    private function normalizeNullableBoolean(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return match (strtolower(trim($value))) {
                '1', 'true', 'yes', 'on' => true,
                '0', 'false', 'no', 'off' => false,
                default => null,
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

        return null;
    }
}
