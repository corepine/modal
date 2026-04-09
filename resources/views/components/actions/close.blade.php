@props([
    'count' => 1,
    'destroy' => true,
    'force' => false,
    'disabled' => false,
])

@php($modalEvents = app(\Corepine\Modal\ModalService::class)->event())
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
@php($resolvedDisabled = is_bool($disabled)
    ? $disabled
    : (is_string($disabled)
        ? match (strtolower(trim($disabled))) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => false,
        }
        : (bool) $disabled))

<button
    type="button"
    x-data
    {{ $attributes }}
    @if ($resolvedDisabled) disabled @endif
    @if (! $resolvedDisabled)
        x-on:click="if (typeof window.corepineModalRequestClose === 'function') {
        window.corepineModalRequestClose({
            count: @js($resolvedCount),
            destroy: @js($resolvedDestroy),
            force: @js($resolvedForce),
        });
    } else if (@js($resolvedForce)) {
        Livewire.dispatch(@js($modalEvents->closeAllModals()), {
            destroy: @js($resolvedDestroy),
        });
    } else {
        Livewire.dispatch(@js($modalEvents->closeModal()), {
            count: @js($resolvedCount),
            destroy: @js($resolvedDestroy),
        });
    }"
    @endif
>
    {{ $slot }}
</button>
