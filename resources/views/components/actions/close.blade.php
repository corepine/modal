@props([
    'count' => 1,
    'destroy' => true,
    'closeAll' => false,
    'disabled' => false,
    'modalId' => null,
    'dispatch' => [],
    'dispatchTo' => [],
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
@php($resolvedCloseAll = is_bool($closeAll)
    ? $closeAll
    : (is_string($closeAll)
        ? match (strtolower(trim($closeAll))) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => false,
        }
        : (bool) $closeAll))
@php($resolvedDisabled = is_bool($disabled)
    ? $disabled
    : (is_string($disabled)
        ? match (strtolower(trim($disabled))) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => false,
        }
        : (bool) $disabled))
@php($resolvedModalId = is_string($modalId) && trim($modalId) !== '' ? trim($modalId) : null)
@php($resolvedDispatch = is_array($dispatch) ? $dispatch : [])
@php($resolvedDispatchTo = is_array($dispatchTo) ? $dispatchTo : [])

<button
    type="button"
    x-data
    {{ $attributes }}
    @if ($resolvedDisabled) disabled @endif
    @if (! $resolvedDisabled)
        x-on:click="const resolvedModalId = @js($resolvedModalId) ?? $el.closest('[data-corepine-modal-id]')?.getAttribute('data-corepine-modal-id') ?? null;
    const closePayload = {
        id: resolvedModalId,
        count: @js($resolvedCount),
        destroy: @js($resolvedDestroy),
        closeAll: @js($resolvedCloseAll),
        dispatch: @js($resolvedDispatch),
        dispatchTo: @js($resolvedDispatchTo),
    };

    if (!@js($resolvedCloseAll) && resolvedModalId) {
        window.dispatchEvent(new CustomEvent(@js($modalEvents->closeModal()), {
            detail: closePayload,
        }));

        if (typeof Livewire?.dispatch === 'function') {
            Livewire.dispatch(@js($modalEvents->closeModal()), closePayload);
        }
    } else if (typeof window.corepineModalRequestClose === 'function') {
        window.corepineModalRequestClose(closePayload);
    } else if (@js($resolvedCloseAll)) {
        if (typeof Livewire?.dispatch === 'function') {
            Livewire.dispatch(@js($modalEvents->closeAllModals()), {
                destroy: @js($resolvedDestroy),
                dispatch: @js($resolvedDispatch),
                dispatchTo: @js($resolvedDispatchTo),
            });
        }
    } else if (typeof Livewire?.dispatch === 'function') {
        Livewire.dispatch(@js($modalEvents->closeModal()), {
            count: @js($resolvedCount),
            destroy: @js($resolvedDestroy),
            dispatch: @js($resolvedDispatch),
            dispatchTo: @js($resolvedDispatchTo),
        });
    }"
    @endif
>
    {{ $slot }}
</button>
