@props([
    'count' => 1,
    'destroy' => true,
    'force' => false,
])

@php($modalConfig = app(\Corepine\Modal\Support\ModalConfig::class))

<div
    x-data
    {{ $attributes }}
    x-on:click="if (@js($force)) {
        Livewire.dispatch(@js($modalConfig->listenEvent('close_all')), {
            destroy: @js($destroy),
        });
    } else {
        Livewire.dispatch(@js($modalConfig->listenEvent('close')), {
            count: @js((int) $count),
            destroy: @js($destroy),
        });
    }"
>
    {{ $slot }}
</div>
