@props([
    'component' => null,
    'componentClass' => null,
    'arguments' => [],
    'modalAttributes' => [],
    'size' => null,
    'modalClass' => null,
])

@php($modalConfig = app(\Corepine\Modal\Support\ModalConfig::class))
@php($targetComponent = $componentClass ?: $component)
@php($payloadModalAttributes = is_array($modalAttributes) ? $modalAttributes : [])
@if (is_string($size) && $size !== '')
    @php($payloadModalAttributes['size'] = $size)
@endif
@php($existingModalClass = isset($payloadModalAttributes['modalClass']) && is_string($payloadModalAttributes['modalClass']) ? $payloadModalAttributes['modalClass'] : '')
@php($incomingModalClass = is_string($modalClass) ? $modalClass : '')
@php($mergedModalClass = trim(implode(' ', array_filter([$existingModalClass, $incomingModalClass]))))
@if ($mergedModalClass !== '')
    @php($payloadModalAttributes['modalClass'] = $mergedModalClass)
@endif

<div
    x-data
    {{ $attributes }}
    x-on:click="Livewire.dispatch(@js($modalConfig->listenEvent('open')), {
        component: @js($targetComponent),
        arguments: @js($arguments),
        modalAttributes: @js($payloadModalAttributes),
    })"
>
    {{ $slot }}
</div>
