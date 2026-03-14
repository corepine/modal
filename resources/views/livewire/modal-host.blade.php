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

        <div class="cp-modal-viewport relative flex min-h-full items-center justify-center p-4 sm:p-8">
            @foreach ($stack as $id)
                @php($modal = $modals[$id] ?? null)
                @continue(! $modal)
                @php($modalClasses = $modalConfig->mergedModalClasses($modal['modalAttributes']))

                <div
                    x-show="activeModalId === @js($id)"
                    x-transition:enter="duration-200 ease-out"
                    x-transition:enter-start="opacity-0 translate-y-6 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="duration-150 ease-in"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                    class="cp-modal-panel-wrap relative w-full"
                    x-ref="{{ $id }}"
                    wire:key="corepine-modal-{{ $id }}"
                >
                    <div @class(['cp-modal-component', 'mx-auto', 'w-full', $modalClasses])>
                        @livewire($modal['name'] ?: $modal['class'], $modal['arguments'], key('corepine-modal-panel-'.$id))
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
