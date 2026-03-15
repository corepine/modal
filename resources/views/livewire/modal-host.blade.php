@php($modalConfig = app(\Corepine\Modal\Support\ModalConfig::class))

<div>
    @once
        <script>
            window.corepineModalHost = (events = {}) => ({
                show: false,
                activeModalId: null,
                listeners: [],

                init() {
                    this.syncFromServer();

                    this.listeners.push(
                        Livewire.on(events.changed, ({ id }) => {
                            this.activeModalId = id ?? null;
                            this.setShow(Boolean(this.activeModalId));
                            this.focusActiveModal();
                        })
                    );

                    this.listeners.push(
                        Livewire.on(events.allClosed, () => {
                            this.activeModalId = null;
                            this.setShow(false);
                        })
                    );
                },

                destroy() {
                    this.listeners.forEach((listener) => listener());
                    this.listeners = [];
                    this.setShow(false);
                },

                syncFromServer() {
                    this.activeModalId = this.$wire.get('activeModalId');
                    this.setShow(Boolean(this.activeModalId));
                    this.focusActiveModal();
                },

                setShow(value) {
                    this.show = value;
                    document.body.classList.toggle('cp-modal-open', value);
                },

                activeModal() {
                    const activeId = this.$wire.get('activeModalId');

                    if (!activeId) {
                        return null;
                    }

                    return this.$wire.get('modals')[activeId] ?? null;
                },

                activeAttributes() {
                    return this.activeModal()?.modalAttributes ?? {};
                },

                blurEnabled() {
                    const attrs = this.activeAttributes();

                    return attrs.blur === true;
                },

                canClose(eventName) {
                    const modal = this.activeModal();

                    if (!modal) {
                        return false;
                    }

                    const payload = {
                        id: this.activeModalId,
                        closing: true,
                    };

                    Livewire.dispatchTo(modal.name ?? modal.class, eventName, payload);

                    return payload.closing !== false;
                },

                closeOnEscape() {
                    const attrs = this.activeAttributes();

                    if (attrs.closeOnEscape !== true) {
                        return;
                    }

                    if (!this.canClose('closingModalOnEscape')) {
                        return;
                    }

                    if (attrs.closeOnEscapeIsForceful === true) {
                        Livewire.dispatch(events.closeAll, {
                            destroy: attrs.destroyOnClose ?? true,
                        });

                        return;
                    }

                    Livewire.dispatch(events.close, {
                        count: 1,
                        destroy: attrs.destroyOnClose ?? true,
                    });
                },

                closeOnClickAway() {
                    const attrs = this.activeAttributes();

                    if (attrs.closeOnClickAway !== true) {
                        return;
                    }

                    if (!this.canClose('closingModalOnClickAway')) {
                        return;
                    }

                    Livewire.dispatch(events.close, {
                        count: 1,
                        destroy: attrs.destroyOnClose ?? true,
                    });
                },

                focusActiveModal() {
                    if (!this.activeModalId) {
                        return;
                    }

                    this.$nextTick(() => {
                        const activeContainer = this.$refs[this.activeModalId];
                        const focusTarget = activeContainer?.querySelector('[autofocus]');

                        if (focusTarget) {
                            focusTarget.focus();
                        }
                    });
                },
            });
        </script>
    @endonce

    <div
        x-data="corepineModalHost({
            changed: @js($modalConfig->dispatchEvent('changed')),
            allClosed: @js($modalConfig->dispatchEvent('all_closed')),
            close: @js($modalConfig->listenEvent('close')),
            closeAll: @js($modalConfig->listenEvent('close_all')),
        })"
        x-cloak
        x-on:keydown.escape.window.stop="closeOnEscape()"
        x-show="show"
        class="cp-modal fixed inset-0 z-[999] overflow-y-auto"
        style="display: none;"
        role="dialog"
        aria-modal="true"
    >
        <div
            class="cp-modal-backdrop absolute inset-0 bg-zinc-950/50"
            x-bind:class="{ 'backdrop-blur-sm': blurEnabled() }"
            x-show="show"
            x-transition.opacity.duration.200ms
            x-on:click="closeOnClickAway()"
        ></div>

        <div class="cp-modal-viewport relative min-h-full">
            @foreach ($stack as $id)
                @php($modal = $modals[$id] ?? null)
                @continue(! $modal)
                @php($modalClasses = $modalConfig->mergedModalClasses($modal['modalAttributes']))
                @php($isDrawer = $modalConfig->isDrawer($modal['modalAttributes']))
                @php($position = $modalConfig->modalPosition($modal['modalAttributes']))
                @php($panelWrapClasses = $modalConfig->modalPanelWrapClasses($modal['modalAttributes']))
                @php($transitionClasses = $modalConfig->modalTransitionClasses($modal['modalAttributes']))

                <div
                    x-show="activeModalId === @js($id)"
                    x-transition:enter="{{ $transitionClasses['enter'] }}"
                    x-transition:enter-start="{{ $transitionClasses['enterStart'] }}"
                    x-transition:enter-end="{{ $transitionClasses['enterEnd'] }}"
                    x-transition:leave="{{ $transitionClasses['leave'] }}"
                    x-transition:leave-start="{{ $transitionClasses['leaveStart'] }}"
                    x-transition:leave-end="{{ $transitionClasses['leaveEnd'] }}"
                    class="{{ $panelWrapClasses }}"
                    x-ref="{{ $id }}"
                    wire:key="corepine-modal-{{ $id }}"
                >
                    <div @class([
                        'cp-modal-component',
                        'w-full',
                        'mx-auto' => ! $isDrawer,
                        'cp-modal-shape-default' => ! $isDrawer,
                        'cp-modal-shape-drawer-left' => $isDrawer && $position === 'left',
                        'cp-modal-shape-drawer-right' => $isDrawer && $position === 'right',
                        'h-full' => $isDrawer,
                        'overflow-y-auto' => $isDrawer,
                        $modalClasses,
                        'rounded-l-none' => $isDrawer && $position === 'left',
                        'rounded-r-none' => $isDrawer && $position === 'right',
                    ])>
                        @livewire($modal['name'] ?: $modal['class'], $modal['arguments'], key('corepine-modal-panel-'.$id))
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
