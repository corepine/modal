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

                activeIsolate() {
                    const attrs = this.activeAttributes();

                    return attrs.isolate === true || attrs.isolated === true;
                },

                shouldShowModal(id) {
                    if (!this.show) {
                        return false;
                    }

                    if (this.activeModalId === id) {
                        return true;
                    }

                    if (!this.activeIsolate()) {
                        return false;
                    }

                    const stack = this.$wire.get('stack') ?? [];
                    const activeIndex = stack.indexOf(this.activeModalId);
                    const idIndex = stack.indexOf(id);

                    return activeIndex !== -1 && idIndex !== -1 && idIndex < activeIndex;
                },

                isTopModal(id) {
                    return this.activeModalId === id;
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
        <div class="cp-modal-viewport relative min-h-full">
            @foreach ($stack as $id)
                @php($modal = $modals[$id] ?? null)
                @continue(! $modal)
                @php($modalClasses = $modalConfig->mergedModalClasses($modal['modalAttributes']))
                @php($isDrawer = $modalConfig->isDrawer($modal['modalAttributes']))
                @php($isSheet = $modalConfig->isSheet($modal['modalAttributes']))
                @php($position = $modalConfig->modalPosition($modal['modalAttributes']))
                @php($hasBlur = (bool) ($modal['modalAttributes']['blur'] ?? false))
                @php($panelWrapClasses = $modalConfig->modalPanelWrapClasses($modal['modalAttributes']))
                @php($transitionClasses = $modalConfig->modalTransitionClasses($modal['modalAttributes']))

                <div
                    x-show="shouldShowModal(@js($id))"
                    x-transition.opacity.duration.200ms
                    x-on:click="if (isTopModal(@js($id))) closeOnClickAway()"
                    x-bind:class="{ 'pointer-events-none': !isTopModal(@js($id)) }"
                    @class([
                        'cp-modal-layer-backdrop absolute inset-0 bg-zinc-950/50',
                        'backdrop-blur-sm' => $hasBlur,
                    ])
                    style="z-index: {{ 20 + ($loop->index * 2) }};"
                    wire:key="corepine-modal-backdrop-{{ $id }}"
                ></div>

                <div
                    x-show="shouldShowModal(@js($id))"
                    x-transition:enter="{{ $transitionClasses['enter'] }}"
                    x-transition:enter-start="{{ $transitionClasses['enterStart'] }}"
                    x-transition:enter-end="{{ $transitionClasses['enterEnd'] }}"
                    x-transition:leave="{{ $transitionClasses['leave'] }}"
                    x-transition:leave-start="{{ $transitionClasses['leaveStart'] }}"
                    x-transition:leave-end="{{ $transitionClasses['leaveEnd'] }}"
                    x-on:click="closeOnClickAway()"
                    x-bind:class="{ 'pointer-events-none': !isTopModal(@js($id)) }"
                    style="z-index: {{ 21 + ($loop->index * 2) }};"
                    class="{{ $panelWrapClasses }}"
                    x-ref="{{ $id }}"
                    wire:key="corepine-modal-{{ $id }}"
                >
                    <div @class([
                        'cp-modal-component',
                        'w-full overflow-hidden bg-white dark:bg-zinc-800',
                        'mx-auto' => ! $isDrawer,
                        'cp-modal-shape-default' => ! $isDrawer && ! $isSheet,
                        'cp-modal-shape-drawer-left' => $isDrawer && $position === 'left',
                        'cp-modal-shape-drawer-right' => $isDrawer && $position === 'right',
                        'cp-modal-shape-sheet' => $isSheet,
                        'max-h-screen  overflow-y-auto' => $isDrawer,
                        'max-h-[88dvh] overflow-y-auto' => $isSheet,
                        $modalClasses,
                        'rounded-l-none' => $isDrawer && $position === 'left',
                        'rounded-r-none' => $isDrawer && $position === 'right',
                    ])
                        x-on:click.stop
                    >
                        @if ($isSheet)
                            <div class="cp-modal-sheet-handle px-4 pt-3 sm:pt-4">
                                <div class="mx-auto h-1.5 w-10 rounded-full bg-zinc-300/80 dark:bg-zinc-600/80"></div>
                            </div>
                        @endif
                        @livewire($modal['name'] ?: $modal['class'], $modal['arguments'], key('corepine-modal-panel-'.$id))
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
