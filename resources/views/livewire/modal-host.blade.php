@php($modalConfig = app(\Corepine\Modal\Support\ModalConfig::class))

<div>
    @once
        <script>
            window.corepineModalHost = (events = {}) => ({
                show: false,
                activeModalId: null,
                listeners: [],
                localClosingIds: [],
                closeTimeout: null,
                requestCloseHandler: null,
                draggingSheetId: null,
                sheetDragStartY: 0,
                sheetDragOffsetY: 0,
                sheetDragPointerId: null,
                defaultSheetDragThreshold: 0.3,
                sheetHeights: {},
                resizingSheetId: null,
                sheetResizeStartY: 0,
                sheetResizeStartHeight: 0,
                sheetResizePointerId: null,
                defaultSheetMinHeight: 260,
                defaultSheetHeightRatio: 0.72,
                defaultSheetTopGap: 16,

                init() {
                    this.syncFromServer();
                    this.requestCloseHandler = (payload = {}) => this.requestClose(payload);
                    window.corepineModalRequestClose = this.requestCloseHandler;

                    this.listeners.push(
                        Livewire.on(events.changed, ({ id }) => {
                            this.activeModalId = id ?? null;
                            this.syncFromServer();
                        })
                    );

                    this.listeners.push(
                        Livewire.on(events.allClosed, () => {
                            this.resetLocalClosing();
                            this.activeModalId = null;
                            this.syncFromServer();
                        })
                    );
                },

                destroy() {
                    this.listeners.forEach((listener) => listener());
                    this.listeners = [];
                    this.resetLocalClosing();

                    if (window.corepineModalRequestClose === this.requestCloseHandler) {
                        delete window.corepineModalRequestClose;
                    }

                    this.setShow(false);
                },

                syncFromServer() {
                    const stack = this.stack();
                    this.activeModalId = this.$wire.get('activeModalId');

                    if (this.localClosingIds.length > 0) {
                        const stillPresent = this.localClosingIds.some((id) => stack.includes(id));

                        if (!stillPresent) {
                            this.resetLocalClosing();
                        }
                    }

                    this.syncSheetHeights();
                    this.setShow(Boolean(this.activeModalId) || this.localClosingIds.length > 0);
                    this.focusActiveModal();
                },

                setShow(value) {
                    this.show = value;
                    document.body.classList.toggle('cp-modal-open', value);
                },

                stack() {
                    return this.$wire.get('stack') ?? [];
                },

                modalById(id) {
                    return this.$wire.get('modals')?.[id] ?? null;
                },

                modalAttributesById(id) {
                    return this.modalById(id)?.modalAttributes ?? {};
                },

                activeModal() {
                    const activeId = this.$wire.get('activeModalId');

                    if (!activeId) {
                        return null;
                    }

                    return this.$wire.get('modals')[activeId] ?? null;
                },

                isLocallyClosing(id) {
                    return this.localClosingIds.includes(id);
                },

                activeAttributes() {
                    return this.activeModal()?.modalAttributes ?? {};
                },

                eventClientY(event) {
                    const point = event?.touches?.[0] ?? event?.changedTouches?.[0] ?? event;

                    return typeof point?.clientY === 'number' ? point.clientY : null;
                },

                viewportHeight() {
                    return window.innerHeight || document.documentElement?.clientHeight || 800;
                },

                normalizeHeightValue(value, fallback = null) {
                    if (typeof value === 'number' && Number.isFinite(value)) {
                        return value;
                    }

                    if (typeof value !== 'string') {
                        return fallback;
                    }

                    const normalized = value.trim().toLowerCase();

                    if (normalized === '') {
                        return fallback;
                    }

                    if (normalized === 'full') {
                        return this.viewportHeight() - this.defaultSheetTopGap;
                    }

                    if (normalized.endsWith('vh') || normalized.endsWith('dvh') || normalized.endsWith('%')) {
                        const ratio = Number.parseFloat(normalized);

                        if (! Number.isNaN(ratio)) {
                            return this.viewportHeight() * (ratio / 100);
                        }

                        return fallback;
                    }

                    if (normalized.endsWith('px')) {
                        const pixels = Number.parseFloat(normalized);

                        return Number.isNaN(pixels) ? fallback : pixels;
                    }

                    const numeric = Number.parseFloat(normalized);

                    if (Number.isNaN(numeric)) {
                        return fallback;
                    }

                    if (numeric > 0 && numeric <= 1) {
                        return this.viewportHeight() * numeric;
                    }

                    return numeric;
                },

                sheetMinHeight(id) {
                    const attrs = this.modalAttributesById(id);
                    const configured = this.normalizeHeightValue(attrs.sheetMinHeight ?? attrs.minHeight, this.defaultSheetMinHeight);

                    return Math.max(120, Math.round(configured ?? this.defaultSheetMinHeight));
                },

                sheetMaxHeight(id) {
                    const attrs = this.modalAttributesById(id);
                    const viewport = this.viewportHeight();
                    const fallback = viewport - this.defaultSheetTopGap;
                    const configured = this.normalizeHeightValue(attrs.sheetMaxHeight ?? attrs.maxHeight, fallback);
                    const max = Math.min(viewport, Math.round(configured ?? fallback));

                    return Math.max(this.sheetMinHeight(id), max);
                },

                clampSheetHeight(height, id) {
                    const min = this.sheetMinHeight(id);
                    const max = this.sheetMaxHeight(id);

                    return Math.max(min, Math.min(max, Math.round(height)));
                },

                resolveInitialSheetHeight(id) {
                    const attrs = this.modalAttributesById(id);
                    const fallback = this.viewportHeight() * this.defaultSheetHeightRatio;
                    const preferred = this.normalizeHeightValue(attrs.sheetHeight ?? attrs.height, fallback);

                    return this.clampSheetHeight(preferred ?? fallback, id);
                },

                ensureSheetHeight(id) {
                    if (!this.isSheetModal(id)) {
                        return;
                    }

                    if (this.sheetHeights[id] === undefined) {
                        this.sheetHeights[id] = this.resolveInitialSheetHeight(id);
                    }
                },

                syncSheetHeights() {
                    const activeStack = this.stack();
                    const activeIds = new Set(activeStack);

                    Object.keys(this.sheetHeights).forEach((id) => {
                        if (!activeIds.has(id)) {
                            delete this.sheetHeights[id];
                        }
                    });

                    activeStack.forEach((id) => {
                        if (this.isSheetModal(id)) {
                            this.ensureSheetHeight(id);
                            this.sheetHeights[id] = this.clampSheetHeight(this.sheetHeights[id], id);
                        }
                    });

                    if (this.resizingSheetId && !activeIds.has(this.resizingSheetId)) {
                        this.clearSheetResize();
                    }
                },

                isSheetModal(id) {
                    const attrs = this.modalAttributesById(id);

                    return attrs.type === 'sheet' || attrs.sheet === true;
                },

                isSheetDraggable(id) {
                    if (!this.isSheetModal(id)) {
                        return false;
                    }

                    const attrs = this.modalAttributesById(id);

                    if (attrs.draggable === undefined || attrs.draggable === null) {
                        return true;
                    }

                    return this.normalizedBoolean(attrs.draggable, true);
                },

                sheetDragThreshold(id) {
                    const attrs = this.modalAttributesById(id);
                    const raw = Number.parseFloat(attrs.dragCloseThreshold ?? attrs.sheetDragThreshold ?? this.defaultSheetDragThreshold);

                    if (Number.isNaN(raw)) {
                        return this.defaultSheetDragThreshold;
                    }

                    return Math.min(0.95, Math.max(0.05, raw));
                },

                startSheetDrag(id, event, fromHandle = false) {
                    if (this.resizingSheetId) {
                        return;
                    }

                    if (!this.isTopModal(id) || !this.shouldShowModal(id) || !this.isSheetDraggable(id)) {
                        return;
                    }

                    if (event?.type === 'mousedown' && event.button !== 0) {
                        return;
                    }

                    if (event?.pointerType === 'mouse' && event.button !== 0) {
                        return;
                    }

                    const startY = this.eventClientY(event);

                    if (startY === null) {
                        return;
                    }

                    this.draggingSheetId = id;
                    this.sheetDragStartY = startY;
                    this.sheetDragOffsetY = 0;
                    this.sheetDragPointerId = event?.pointerId ?? null;

                    if (fromHandle && event?.cancelable) {
                        event.preventDefault();
                    }
                },

                moveSheetDrag(event) {
                    if (this.resizingSheetId) {
                        this.moveSheetResize(event);

                        return;
                    }

                    if (!this.draggingSheetId) {
                        return;
                    }

                    if (this.sheetDragPointerId !== null && event?.pointerId !== undefined && event.pointerId !== this.sheetDragPointerId) {
                        return;
                    }

                    const currentY = this.eventClientY(event);

                    if (currentY === null) {
                        return;
                    }

                    const deltaY = Math.max(0, currentY - this.sheetDragStartY);
                    this.sheetDragOffsetY = deltaY;

                    if (deltaY > 0 && event?.cancelable) {
                        event.preventDefault();
                    }
                },

                clearSheetDrag() {
                    this.draggingSheetId = null;
                    this.sheetDragStartY = 0;
                    this.sheetDragOffsetY = 0;
                    this.sheetDragPointerId = null;
                },

                endSheetDrag(event) {
                    if (this.resizingSheetId) {
                        this.endSheetResize(event);

                        return;
                    }

                    if (!this.draggingSheetId) {
                        return;
                    }

                    if (this.sheetDragPointerId !== null && event?.pointerId !== undefined && event.pointerId !== this.sheetDragPointerId) {
                        return;
                    }

                    const id = this.draggingSheetId;
                    const panel = this.$refs[`panel-${id}`];
                    const panelHeight = panel?.offsetHeight ?? window.innerHeight;
                    const threshold = panelHeight * this.sheetDragThreshold(id);
                    const shouldClose = this.sheetDragOffsetY >= threshold;
                    const attrs = this.modalAttributesById(id);
                    const destroyOnClose = attrs.destroyOnClose ?? true;

                    this.clearSheetDrag();

                    if (!shouldClose) {
                        return;
                    }

                    this.requestClose({
                        id,
                        count: 1,
                        destroy: destroyOnClose,
                    });
                },

                startSheetResize(id, event) {
                    if (this.draggingSheetId) {
                        return;
                    }

                    if (!this.isTopModal(id) || !this.shouldShowModal(id) || !this.isSheetDraggable(id)) {
                        return;
                    }

                    if (event?.type === 'mousedown' && event.button !== 0) {
                        return;
                    }

                    if (event?.pointerType === 'mouse' && event.button !== 0) {
                        return;
                    }

                    const startY = this.eventClientY(event);

                    if (startY === null) {
                        return;
                    }

                    this.ensureSheetHeight(id);
                    this.resizingSheetId = id;
                    this.sheetResizeStartY = startY;
                    this.sheetResizeStartHeight = this.sheetHeights[id] ?? this.resolveInitialSheetHeight(id);
                    this.sheetResizePointerId = event?.pointerId ?? null;

                    if (event?.cancelable) {
                        event.preventDefault();
                    }
                },

                moveSheetResize(event) {
                    if (!this.resizingSheetId) {
                        return;
                    }

                    if (this.sheetResizePointerId !== null && event?.pointerId !== undefined && event.pointerId !== this.sheetResizePointerId) {
                        return;
                    }

                    const currentY = this.eventClientY(event);

                    if (currentY === null) {
                        return;
                    }

                    const deltaY = currentY - this.sheetResizeStartY;
                    const nextHeight = this.sheetResizeStartHeight - deltaY;
                    this.sheetHeights[this.resizingSheetId] = this.clampSheetHeight(nextHeight, this.resizingSheetId);

                    if (event?.cancelable) {
                        event.preventDefault();
                    }
                },

                clearSheetResize() {
                    this.resizingSheetId = null;
                    this.sheetResizeStartY = 0;
                    this.sheetResizeStartHeight = 0;
                    this.sheetResizePointerId = null;
                },

                endSheetResize(event) {
                    if (!this.resizingSheetId) {
                        return;
                    }

                    if (this.sheetResizePointerId !== null && event?.pointerId !== undefined && event.pointerId !== this.sheetResizePointerId) {
                        return;
                    }

                    this.clearSheetResize();
                },

                sheetPanelStyle(id) {
                    if (!this.isSheetModal(id)) {
                        return '';
                    }

                    this.ensureSheetHeight(id);
                    const isDraggingCurrent = this.draggingSheetId === id;
                    const isResizingCurrent = this.resizingSheetId === id;
                    const offset = isDraggingCurrent ? this.sheetDragOffsetY : 0;
                    const transition = (isDraggingCurrent || isResizingCurrent)
                        ? 'none'
                        : 'transform 180ms ease-out, height 140ms ease-out';
                    const height = this.sheetHeights[id] ?? this.resolveInitialSheetHeight(id);

                    return `height: ${height}px; max-height: calc(100dvh - ${this.defaultSheetTopGap}px); transform: translate3d(0, ${offset}px, 0); transition: ${transition};`;
                },

                activeIsolate() {
                    const attrs = this.activeAttributes();

                    return attrs.isolate === true || attrs.isolated === true;
                },

                shouldShowModal(id) {
                    if (this.isLocallyClosing(id)) {
                        return false;
                    }

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

                normalizedBoolean(value, fallback = false) {
                    if (typeof value === 'boolean') {
                        return value;
                    }

                    if (typeof value === 'string') {
                        const normalized = value.trim().toLowerCase();

                        if (['1', 'true', 'yes', 'on'].includes(normalized)) return true;
                        if (['0', 'false', 'no', 'off'].includes(normalized)) return false;
                    }

                    return fallback;
                },

                planClosingIds(payload = {}) {
                    const stack = this.stack();

                    if (stack.length === 0) {
                        return [];
                    }

                    if (payload.force === true) {
                        return [...stack];
                    }

                    if (typeof payload.id === 'string' && payload.id !== '') {
                        const position = stack.indexOf(payload.id);

                        if (position !== -1) {
                            return stack.slice(position);
                        }
                    }

                    const parsedCount = Number.parseInt(payload.count ?? 1, 10);
                    const layers = Number.isNaN(parsedCount) ? 1 : Math.max(1, parsedCount);

                    return stack.slice(-layers);
                },

                resetLocalClosing() {
                    if (this.closeTimeout) {
                        clearTimeout(this.closeTimeout);
                        this.closeTimeout = null;
                    }

                    this.localClosingIds = [];
                    this.clearSheetDrag();
                    this.clearSheetResize();
                },

                requestClose(payload = {}) {
                    if (this.localClosingIds.length > 0) {
                        return;
                    }

                    const force = this.normalizedBoolean(payload.force ?? false, false);
                    const destroy = this.normalizedBoolean(payload.destroy ?? true, true);
                    const id = typeof payload.id === 'string' && payload.id !== '' ? payload.id : null;
                    const closingIds = this.planClosingIds({
                        id,
                        count: payload.count ?? 1,
                        force,
                    });

                    if (closingIds.length === 0) {
                        if (force) {
                            Livewire.dispatch(events.closeAll, { destroy });
                        } else {
                            Livewire.dispatch(events.close, {
                                id,
                                count: Math.max(1, Number.parseInt(payload.count ?? 1, 10) || 1),
                                destroy,
                            });
                        }

                        return;
                    }

                    this.localClosingIds = closingIds;
                    this.setShow(true);

                    this.closeTimeout = setTimeout(() => {
                        this.closeTimeout = null;

                        if (force) {
                            Livewire.dispatch(events.closeAll, { destroy });
                        } else {
                            Livewire.dispatch(events.close, {
                                id,
                                count: Math.max(1, closingIds.length),
                                destroy,
                            });
                        }
                    }, 260);
                },

                canClose(eventName) {
                    if (this.localClosingIds.length > 0) {
                        return false;
                    }

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
                        this.requestClose({
                            force: true,
                            destroy: attrs.destroyOnClose ?? true,
                        });

                        return;
                    }

                    this.requestClose({
                        id: this.activeModalId,
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

                    this.requestClose({
                        id: this.activeModalId,
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
        x-on:pointermove.window="moveSheetDrag($event)"
        x-on:pointerup.window="endSheetDrag($event)"
        x-on:pointercancel.window="endSheetDrag($event)"
        x-on:touchmove.window="moveSheetDrag($event)"
        x-on:touchend.window="endSheetDrag($event)"
        x-on:touchcancel.window="endSheetDrag($event)"
        x-on:mousemove.window="moveSheetDrag($event)"
        x-on:mouseup.window="endSheetDrag($event)"
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
                        x-ref="panel-{{ $id }}"
                        x-bind:style="sheetPanelStyle(@js($id))"
                        x-on:pointerdown.capture="startSheetDrag(@js($id), $event)"
                        x-on:touchstart.capture="startSheetDrag(@js($id), $event)"
                        x-on:mousedown.capture="startSheetDrag(@js($id), $event)"
                        x-on:click.stop
                    >
                        @if ($isSheet)
                            <div
                                class="cp-modal-sheet-handle cursor-row-resize select-none px-4 pt-3 sm:pt-4"
                                x-on:pointerdown.stop.prevent="startSheetResize(@js($id), $event)"
                                x-on:touchstart.stop.prevent="startSheetResize(@js($id), $event)"
                                x-on:mousedown.stop.prevent="startSheetResize(@js($id), $event)"
                                style="touch-action: none;"
                            >
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
