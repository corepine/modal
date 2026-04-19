@props([
    'component' => null,
    'modalId' => null,
    'arguments' => [],
    'modalAttributes' => [],
    'size' => null,
    'blur' => null,
    'type' => null,
    'drawer' => null,
    'sheet' => null,
    'bottomSheet' => null,
    'placement' => null,
    'isolate' => null,
    'origin' => null,
    'height' => null,
    'maxHeight' => null,
    'closeOnEscape' => null,
    'dismissible' => null,
    'closeAllOnEscape' => null,
    'draggable' => null,
    'showDragHandle' => null,
    'dragCloseThreshold' => null,
    'shell' => null,
    'heading' => null,
    'description' => null,
    'showClose' => null,
    'actions' => null,
    'footerActionsAlignment' => null,
    'dispatch' => null,
    'dispatchTo' => null,
])

@php($modalEvents = app(\Corepine\Modal\ModalService::class)->event())
@php($triggerAttributes = $attributes->except('class'))
@php($payloadModalAttributes = is_array($modalAttributes) ? $modalAttributes : [])
@php($resolvedModalId = is_string($modalId) && trim($modalId) !== '' ? trim($modalId) : null)
@php($normalizeBoolean = static function (mixed $value): ?bool {
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
})
@if (is_string($size) && $size !== '')
    @php($payloadModalAttributes['size'] = $size)
@endif
@php($normalizedBlur = null)
@if (is_bool($blur))
    @php($normalizedBlur = $blur)
@elseif (is_string($blur))
    @php($normalizedBlur = match (strtolower(trim($blur))) {
        '1', 'true', 'yes', 'on' => true,
        '0', 'false', 'no', 'off' => false,
        default => null,
    })
@endif
@if (! is_null($normalizedBlur))
    @php($payloadModalAttributes['blur'] = $normalizedBlur)
@endif
@php($normalizedType = null)
@if ($type instanceof \Corepine\Modal\Enums\ModalType)
    @php($normalizedType = $type->value)
@elseif (is_string($type))
    @php($normalizedType = match (strtolower(trim($type))) {
        'bottomsheet', 'bottom-sheet', 'bottom_sheet' => 'sheet',
        'modal', 'drawer', 'sheet' => strtolower(trim($type)),
        default => null,
    })
@endif
@php($normalizedDrawer = null)
@if (is_bool($drawer))
    @php($normalizedDrawer = $drawer)
@elseif (is_string($drawer))
    @php($normalizedDrawer = match (strtolower(trim($drawer))) {
        '1', 'true', 'yes', 'on' => true,
        '0', 'false', 'no', 'off' => false,
        default => null,
    })
@endif
@if (! is_null($normalizedDrawer))
    @php($payloadModalAttributes['drawer'] = $normalizedDrawer)
    @if (is_null($normalizedType) && $normalizedDrawer === true)
        @php($normalizedType = 'drawer')
    @endif
@endif
@php($normalizedSheet = null)
@if (is_bool($sheet))
    @php($normalizedSheet = $sheet)
@elseif (is_string($sheet))
    @php($normalizedSheet = match (strtolower(trim($sheet))) {
        '1', 'true', 'yes', 'on' => true,
        '0', 'false', 'no', 'off' => false,
        default => null,
    })
@endif
@if (! is_null($normalizedSheet))
    @php($payloadModalAttributes['sheet'] = $normalizedSheet)
    @if (is_null($normalizedType) && $normalizedSheet === true)
        @php($normalizedType = 'sheet')
    @endif
@endif
@php($normalizedBottomSheet = $normalizeBoolean($bottomSheet))
@if (! is_null($normalizedBottomSheet))
    @php($payloadModalAttributes['bottomSheet'] = $normalizedBottomSheet)
    @if (is_null($normalizedType) && $normalizedBottomSheet === true)
        @php($normalizedType = 'sheet')
    @endif
@endif
@php($rawIsolate = $isolate)
@php($normalizedIsolate = null)
@if (is_bool($rawIsolate))
    @php($normalizedIsolate = $rawIsolate)
@elseif (is_string($rawIsolate))
    @php($normalizedIsolate = match (strtolower(trim($rawIsolate))) {
        '1', 'true', 'yes', 'on' => true,
        '0', 'false', 'no', 'off' => false,
        default => null,
    })
@endif
@if (! is_null($normalizedIsolate))
    @php($payloadModalAttributes['isolate'] = $normalizedIsolate)
@endif
@if (! is_null($normalizedType))
    @php($payloadModalAttributes['type'] = $normalizedType)
@endif
@if (is_string($placement) && trim($placement) !== '')
    @php($payloadModalAttributes['placement'] = strtolower(trim($placement)))
@endif
@if (is_string($origin) && trim($origin) !== '')
    @php($payloadModalAttributes['origin'] = strtolower(trim($origin)))
@endif
@if (is_int($height) || is_float($height))
    @php($payloadModalAttributes['height'] = $height)
@elseif (is_string($height) && trim($height) !== '')
    @php($payloadModalAttributes['height'] = trim($height))
@endif
@if (is_int($maxHeight) || is_float($maxHeight))
    @php($payloadModalAttributes['maxHeight'] = $maxHeight)
@elseif (is_string($maxHeight) && trim($maxHeight) !== '')
    @php($payloadModalAttributes['maxHeight'] = trim($maxHeight))
@endif
@php($rawShell = $shell)
@php($normalizedShell = null)
@if (is_bool($rawShell))
    @php($normalizedShell = $rawShell)
@elseif (is_string($rawShell))
    @php($normalizedShell = match (strtolower(trim($rawShell))) {
        '1', 'true', 'yes', 'on' => true,
        '0', 'false', 'no', 'off' => false,
        default => null,
    })
@endif
@if (! is_null($normalizedShell))
    @php($payloadModalAttributes['shell'] = $normalizedShell)
@endif
@php($normalizedCloseOnEscape = $normalizeBoolean($closeOnEscape))
@if (! is_null($normalizedCloseOnEscape))
    @php($payloadModalAttributes['closeOnEscape'] = $normalizedCloseOnEscape)
@endif
@php($normalizedDismissible = $normalizeBoolean($dismissible))
@if (! is_null($normalizedDismissible))
    @php($payloadModalAttributes['dismissible'] = $normalizedDismissible)
@endif
@php($normalizedCloseAllOnEscape = $normalizeBoolean($closeAllOnEscape))
@if (! is_null($normalizedCloseAllOnEscape))
    @php($payloadModalAttributes['closeAllOnEscape'] = $normalizedCloseAllOnEscape)
@endif
@php($normalizedDraggable = ! is_null($draggable) ? $normalizeBoolean($draggable) : null)
@if (! is_null($normalizedDraggable))
    @php($payloadModalAttributes['draggable'] = $normalizedDraggable)
@endif
@php($normalizedShowDragHandle = ! is_null($showDragHandle) ? $normalizeBoolean($showDragHandle) : null)
@if (! is_null($normalizedShowDragHandle))
    @php($payloadModalAttributes['showDragHandle'] = $normalizedShowDragHandle)
@endif
@if (is_int($dragCloseThreshold) || is_float($dragCloseThreshold))
    @php($payloadModalAttributes['dragCloseThreshold'] = $dragCloseThreshold)
@elseif (is_string($dragCloseThreshold) && trim($dragCloseThreshold) !== '')
    @php($payloadModalAttributes['dragCloseThreshold'] = trim($dragCloseThreshold))
@endif
@if (is_string($heading) && trim($heading) !== '')
    @php($payloadModalAttributes['heading'] = trim($heading))
@endif
@if (is_string($description) && trim($description) !== '')
    @php($payloadModalAttributes['description'] = trim($description))
@endif
@php($normalizedShowClose = null)
@if (is_bool($showClose))
    @php($normalizedShowClose = $showClose)
@elseif (is_string($showClose))
    @php($normalizedShowClose = match (strtolower(trim($showClose))) {
        '1', 'true', 'yes', 'on' => true,
        '0', 'false', 'no', 'off' => false,
        default => null,
    })
@endif
@if (! is_null($normalizedShowClose))
    @php($payloadModalAttributes['showClose'] = $normalizedShowClose)
@endif
@if ($footerActionsAlignment instanceof \Corepine\Support\Enums\Alignment)
    @php($payloadModalAttributes['footerActionsAlignment'] = $footerActionsAlignment->value)
@elseif (is_string($footerActionsAlignment) && trim($footerActionsAlignment) !== '')
    @php($payloadModalAttributes['footerActionsAlignment'] = trim($footerActionsAlignment))
@endif
@if (is_array($actions) && $actions !== [])
    @php($payloadModalAttributes['actions'] = $actions)
@endif
@if (is_array($dispatch) && $dispatch !== [])
    @php($payloadModalAttributes['dispatch'] = $dispatch)
@endif
@if (is_array($dispatchTo) && $dispatchTo !== [])
    @php($payloadModalAttributes['dispatchTo'] = $dispatchTo)
@endif
@php($existingClass = isset($payloadModalAttributes['class']) && is_string($payloadModalAttributes['class']) ? $payloadModalAttributes['class'] : '')
@php($incomingClass = is_string($attributes->get('class')) ? $attributes->get('class') : '')
@php($mergedClass = trim(implode(' ', array_filter([$existingClass, $incomingClass]))))
@if ($mergedClass !== '')
    @php($payloadModalAttributes['class'] = $mergedClass)
@endif

<div
    x-data
    {{ $triggerAttributes }}
    x-on:click="const standalonePayload = { id: @js($resolvedModalId) };
    const stackPayload = {
        component: @js($component),
        arguments: @js($arguments),
        modalAttributes: @js($payloadModalAttributes),
    };

    if (@js($resolvedModalId)) {
        window.dispatchEvent(new CustomEvent(@js($modalEvents->openModal()), {
            detail: standalonePayload,
        }));

        if (typeof Livewire?.dispatch === 'function') {
            Livewire.dispatch(@js($modalEvents->openModal()), standalonePayload);
        }
    } else if (typeof Livewire?.dispatch === 'function') {
        Livewire.dispatch(@js($modalEvents->openModal()), stackPayload);
    }"
>
    {{ $slot }}
</div>
