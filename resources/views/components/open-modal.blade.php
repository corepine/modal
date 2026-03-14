@props([
    'component' => null,
    'componentClass' => null,
    'arguments' => [],
    'modalAttributes' => [],
])

@php($modalConfig = app(\Corepine\Modal\Support\ModalConfig::class))
@php($targetComponent = $componentClass ?: $component)

<div
    x-data
    {{ $attributes }}
    x-on:click="Livewire.dispatch(@js($modalConfig->listenEvent('open')), {
        component: @js($targetComponent),
        arguments: @js($arguments),
        modalAttributes: @js($modalAttributes),
    })"
>
    {{ $slot }}
</div>
