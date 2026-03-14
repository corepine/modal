@props([
    'component' => null,
    'componentClass' => null,
    'arguments' => [],
    'modalAttributes' => [],
    'size' => null,
    'blur' => null,
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
