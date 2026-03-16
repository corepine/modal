@props([
    'count' => 1,
    'destroy' => true,
    'force' => false,
])

@php($modalConfig = app(\Corepine\Modal\Support\ModalConfig::class))
@php($resolvedCount = is_numeric($count) ? max(1, (int) $count) : 1)
@php($resolvedDestroy = is_bool($destroy)
    ? $destroy
    : (is_string($destroy)
        ? match (strtolower(trim($destroy))) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => true,
        }
        : (bool) $destroy))
@php($resolvedForce = is_bool($force)
    ? $force
    : (is_string($force)
        ? match (strtolower(trim($force))) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => false,
        }
        : (bool) $force))

<div
    x-data
    {{ $attributes }}
    x-on:click="if (typeof window.corepineModalRequestClose === 'function') {
        window.corepineModalRequestClose({
            count: @js($resolvedCount),
            destroy: @js($resolvedDestroy),
            force: @js($resolvedForce),
        });
    } else if (@js($resolvedForce)) {
        Livewire.dispatch(@js($modalConfig->listenEvent('close_all')), {
            destroy: @js($resolvedDestroy),
        });
    } else {
        Livewire.dispatch(@js($modalConfig->listenEvent('close')), {
            count: @js($resolvedCount),
            destroy: @js($resolvedDestroy),
        });
    }"
>
    {{ $slot }}
</div>
