@props([
    'component' => null,
    'componentClass' => null,
    'arguments' => [],
    'modalAttributes' => [],
    'size' => null,
    'sizeClasses' => null,
    'panelClass' => null,
])

@php($modalConfig = app(\Corepine\Modal\Support\ModalConfig::class))
@php($targetComponent = $componentClass ?: $component)
@php($payloadModalAttributes = is_array($modalAttributes) ? $modalAttributes : [])
@if (is_string($size) && $size !== '')
    @php($payloadModalAttributes['size'] = $size)
@endif
@if (is_string($sizeClasses) && $sizeClasses !== '')
    @php($payloadModalAttributes['sizeClasses'] = $sizeClasses)
@endif
@if (is_string($panelClass) && $panelClass !== '')
    @php($existingPanelClass = isset($payloadModalAttributes['panelClass']) && is_string($payloadModalAttributes['panelClass']) ? $payloadModalAttributes['panelClass'] : '')
    @php($payloadModalAttributes['panelClass'] = trim($existingPanelClass.' '.$panelClass))
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
