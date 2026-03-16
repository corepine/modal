@props([
    'component' => null,
    'componentClass' => null,
    'arguments' => [],
    'modalAttributes' => [],
    'size' => null,
    'blur' => null,
    'type' => null,
    'drawer' => null,
    'sheet' => null,
    'isolate' => null,
    'isolated' => null,
    'position' => null,
])

@php($modalConfig = app(\Corepine\Modal\Support\ModalConfig::class))
@php($targetComponent = $componentClass ?: $component)
@php($triggerAttributes = $attributes->except('class'))
@php($payloadModalAttributes = is_array($modalAttributes) ? $modalAttributes : [])
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
@php($rawIsolate = ! is_null($isolate) ? $isolate : $isolated)
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
@if (is_string($position) && trim($position) !== '')
    @php($payloadModalAttributes['position'] = strtolower(trim($position)))
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
    x-on:click="Livewire.dispatch(@js($modalConfig->listenEvent('open')), {
        component: @js($targetComponent),
        arguments: @js($arguments),
        modalAttributes: @js($payloadModalAttributes),
    })"
>
    {{ $slot }}
</div>
