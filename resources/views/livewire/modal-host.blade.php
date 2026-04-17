@php
    $modalConfig = app(\Corepine\Modal\Support\ModalConfig::class);
@endphp

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
                viewportVersion: 0,
                defaultSheetMinHeight: 260,
                defaultSheetHeightRatio: 0.7,
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
                    document.body.classList.toggle('overflow-hidden', value);
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

                isDismissible() {
                    const attrs = this.activeAttributes();

                    return this.normalizedBoolean(attrs.dismissible ?? true, true);
                },

                shouldCloseAllOnEscape() {
                    const attrs = this.activeAttributes();

                    return this.normalizedBoolean(attrs.closeAllOnEscape ?? false, false);
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
                        if (value > 0 && value <= 1) {
                            return this.viewportHeight() * value;
                        }

                        if (value > 1 && value <= 100) {
                            return this.viewportHeight() * (value / 100);
                        }

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

                    if (numeric > 1 && numeric <= 100) {
                        return this.viewportHeight() * (numeric / 100);
                    }

                    return numeric;
                },

                normalizePanelHeightValue(value, fallback = null) {
                    if (typeof value === 'number' && Number.isFinite(value)) {
                        if (value > 0 && value <= 1) {
                            return `${value * 100}dvh`;
                        }

                        if (value > 1 && value <= 100) {
                            return `${value}dvh`;
                        }

                        return `${value}px`;
                    }

                    if (typeof value !== 'string') {
                        return fallback;
                    }

                    const normalized = value.trim();

                    if (normalized === '') {
                        return fallback;
                    }

                    if (normalized.includes(' ')) {
                        return fallback;
                    }

                    const lowered = normalized.toLowerCase();

                    if (lowered === 'full' || lowered === 'h-full') {
                        return '100%';
                    }

                    if (lowered === 'h-screen') {
                        return '100vh';
                    }

                    if (lowered === 'h-dvh') {
                        return '100dvh';
                    }

                    if (lowered === 'h-svh') {
                        return '100svh';
                    }

                    if (lowered === 'h-lvh') {
                        return '100lvh';
                    }

                    const arbitraryHeight = lowered.match(/^h-\[(.+)\]$/);

                    if (arbitraryHeight?.[1]) {
                        return arbitraryHeight[1];
                    }

                    const fractionHeight = lowered.match(/^h-(\d+)\/(\d+)$/);

                    if (fractionHeight) {
                        const numerator = Number.parseFloat(fractionHeight[1]);
                        const denominator = Number.parseFloat(fractionHeight[2]);

                        if (denominator > 0) {
                            return `${(numerator / denominator) * 100}%`;
                        }
                    }

                    if (
                        lowered.startsWith('calc(')
                        || lowered.endsWith('px')
                        || lowered.endsWith('rem')
                        || lowered.endsWith('em')
                        || lowered.endsWith('vh')
                        || lowered.endsWith('dvh')
                        || lowered.endsWith('svh')
                        || lowered.endsWith('lvh')
                        || lowered.endsWith('%')
                    ) {
                        return lowered;
                    }

                    const numeric = Number.parseFloat(lowered);

                    if (Number.isNaN(numeric)) {
                        return fallback;
                    }

                    if (numeric > 0 && numeric <= 1) {
                        return `${numeric * 100}dvh`;
                    }

                    if (numeric > 1 && numeric <= 100) {
                        return `${numeric}dvh`;
                    }

                    return `${numeric}px`;
                },

                classHeightHint(value) {
                    if (typeof value !== 'string' || value.trim() === '') {
                        return null;
                    }

                    const tokens = value.trim().split(/\s+/);
                    let heightToken = null;

                    for (const token of tokens) {
                        if (
                            /^h-\[.+\]$/.test(token)
                            || /^h-\d+\/\d+$/.test(token)
                            || ['h-full', 'h-screen', 'h-dvh', 'h-svh', 'h-lvh'].includes(token)
                        ) {
                            heightToken = token;
                        }
                    }

                    if (!heightToken) {
                        return null;
                    }

                    if (heightToken === 'h-full') {
                        return 'full';
                    }

                    if (heightToken === 'h-screen') {
                        return '100vh';
                    }

                    if (heightToken === 'h-dvh') {
                        return '100dvh';
                    }

                    if (heightToken === 'h-svh') {
                        return '100svh';
                    }

                    if (heightToken === 'h-lvh') {
                        return '100lvh';
                    }

                    const arbitrary = heightToken.match(/^h-\[(.+)\]$/);

                    if (arbitrary?.[1]) {
                        return arbitrary[1];
                    }

                    const fraction = heightToken.match(/^h-(\d+)\/(\d+)$/);

                    if (fraction) {
                        const numerator = Number.parseFloat(fraction[1]);
                        const denominator = Number.parseFloat(fraction[2]);

                        if (denominator > 0) {
                            return `${(numerator / denominator) * 100}%`;
                        }
                    }

                    return null;
                },

                modalType(id) {
                    const attrs = this.modalAttributesById(id);

                    if (attrs.type === 'drawer' || attrs.drawer === true) {
                        return 'drawer';
                    }

                    if (attrs.type === 'sheet' || attrs.sheet === true) {
                        return 'sheet';
                    }

                    return 'modal';
                },

                sheetMinHeight(id) {
                    return Math.max(120, Math.round(this.defaultSheetMinHeight));
                },

                sheetMaxHeight(id) {
                    const attrs = this.modalAttributesById(id);
                    const viewport = this.viewportHeight();
                    const fallback = viewport - this.defaultSheetTopGap;
                    const configured = this.normalizeHeightValue(attrs.maxHeight ?? null, fallback);
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
                    const classPreferred = this.classHeightHint(attrs.class ?? '');
                    const preferredSource = attrs.height ?? null;
                    const normalizedPreferredSource = this.classHeightHint(
                        typeof preferredSource === 'string' ? preferredSource : ''
                    ) ?? preferredSource;
                    const preferred = this.normalizeHeightValue(
                        normalizedPreferredSource ?? classPreferred,
                        fallback
                    );

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

                handleViewportResize() {
                    this.viewportVersion += 1;
                    this.syncSheetHeights();
                },

                isSheetModal(id) {
                    return this.modalType(id) === 'sheet';
                },

                isSheetDraggable(id) {
                    if (!this.isSheetModal(id)) {
                        return false;
                    }

                    const attrs = this.modalAttributesById(id);
                    const draggable = attrs.draggable;

                    if (draggable === undefined || draggable === null) {
                        return true;
                    }

                    return this.normalizedBoolean(draggable, true);
                },

                shouldShowSheetDragHandle(id) {
                    if (!this.isSheetDraggable(id)) {
                        return false;
                    }

                    const attrs = this.modalAttributesById(id);
                    const showDragHandle = attrs.showDragHandle ?? attrs.draggable ?? true;

                    return this.normalizedBoolean(showDragHandle, true);
                },

                sheetDragThreshold(id) {
                    const attrs = this.modalAttributesById(id);
                    const raw = Number.parseFloat(attrs.dragCloseThreshold ?? this.defaultSheetDragThreshold);

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
                    const releaseY = this.eventClientY(event);

                    if (releaseY !== null) {
                        const releaseOffset = Math.max(0, releaseY - this.sheetDragStartY);
                        this.sheetDragOffsetY = Math.max(this.sheetDragOffsetY, releaseOffset);
                    }

                    const finalOffset = this.sheetDragOffsetY;
                    const panelHeight = panel?.offsetHeight ?? window.innerHeight;
                    const threshold = panelHeight * this.sheetDragThreshold(id);
                    const shouldClose = finalOffset >= threshold;
                    const attrs = this.modalAttributesById(id);
                    const destroyOnClose = attrs.destroyOnClose ?? true;

                    if (!shouldClose) {
                        this.clearSheetDrag();

                        return;
                    }

                    if (this.localClosingIds.length > 0) {
                        return;
                    }

                    // Keep moving down when threshold is reached so close feels continuous.
                    this.sheetDragOffsetY = Math.max(finalOffset, panelHeight);
                    this.sheetDragPointerId = null;
                    this.sheetDragStartY = 0;

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
                    const maxHeight = this.sheetMaxHeight(id);

                    return `height: ${height}px; max-height: ${maxHeight}px; transform: translate3d(0, ${offset}px, 0); transition: ${transition};`;
                },

                nonSheetPanelStyle(id) {
                    if (this.isSheetModal(id)) {
                        return '';
                    }

                    const attrs = this.modalAttributesById(id);
                    const explicitHeight = this.normalizePanelHeightValue(attrs.height ?? null, null);
                    const explicitMaxHeight = this.normalizePanelHeightValue(attrs.maxHeight ?? null, null);

                    if (!explicitHeight && !explicitMaxHeight) {
                        return '';
                    }

                    const styles = [];

                    if (explicitHeight) {
                        styles.push(`height: ${explicitHeight}`);
                    }

                    if (explicitMaxHeight) {
                        styles.push(`max-height: ${explicitMaxHeight}`);
                    } else if (this.modalType(id) === 'drawer' && explicitHeight) {
                        styles.push('max-height: 100dvh');
                    }

                    return `${styles.join('; ')};`;
                },

                panelStyle(id) {
                    if (this.isSheetModal(id)) {
                        const viewportVersion = this.viewportVersion;
                        void viewportVersion;

                        return this.sheetPanelStyle(id);
                    }

                    return this.nonSheetPanelStyle(id);
                },

                activeIsolate() {
                    const attrs = this.activeAttributes();

                    return attrs.isolate === true;
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

                    if (payload.closeAll === true) {
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

                    const closeAll = this.normalizedBoolean(payload.closeAll ?? false, false);
                    const destroy = this.normalizedBoolean(payload.destroy ?? true, true);
                    const id = typeof payload.id === 'string' && payload.id !== '' ? payload.id : null;
                    const closingIds = this.planClosingIds({
                        id,
                        count: payload.count ?? 1,
                        closeAll,
                    });

                    if (closingIds.length === 0) {
                        if (closeAll) {
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

                        if (closeAll) {
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

                    if (this.shouldCloseAllOnEscape()) {
                        this.requestClose({
                            closeAll: true,
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

                handleClickAway() {
                    if (!this.isDismissible()) {
                        return;
                    }

                    const attrs = this.activeAttributes();

                    if (!this.canClose('closingModalOnClickAway')) {
                        return;
                    }

                    this.requestClose({
                        id: this.activeModalId,
                        count: 1,
                        destroy: attrs.destroyOnClose ?? true,
                    });
                },

                callModalMethod(id, method, params = []) {
                    if (typeof method !== 'string' || method.trim() === '') {
                        return;
                    }

                    const panel = this.$refs[`panel-${id}`];
                    const livewireRoot = panel?.querySelector('[data-corepine-modal-livewire] [wire\\:id]');
                    const componentId = livewireRoot?.getAttribute('wire:id');

                    if (!componentId || typeof Livewire?.find !== 'function') {
                        return;
                    }

                    const component = Livewire.find(componentId);

                    if (!component || typeof component.call !== 'function') {
                        return;
                    }

                    const payload = Array.isArray(params) ? params : [params];
                    component.call(method, ...payload);
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

            if (typeof globalThis !== 'undefined') {
                globalThis.corepineModalHost = window.corepineModalHost;
            }
        </script>
    @endonce

    <div
        x-data="window.corepineModalHost({
            open: @js($modalConfig->listenEvent('open')),
            openSheet: @js($modalConfig->listenEvent('open_sheet')),
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
        x-on:resize.window.debounce.120ms="handleViewportResize()"
        x-show="show"
        class="fixed inset-0 z-[999] overflow-y-auto"
        style="display: none;"
        role="dialog"
        aria-modal="true"
    >
        <div class="relative min-h-full">
            @foreach ($stack as $id)
                @php
                    $modal = $modals[$id] ?? null;
                @endphp
                @continue(! $modal)
                @php
                    $modalClasses = $modalConfig->mergedModalClasses($modal['modalAttributes']);
                    $isDrawer = $modalConfig->isDrawer($modal['modalAttributes']);
                    $isSheet = $modalConfig->isSheet($modal['modalAttributes']);
                    $position = $modalConfig->modalPosition($modal['modalAttributes']);
                    $originClass = $modalConfig->modalOriginClass($modal['modalAttributes']);
                    $hasBlur = (bool) ($modal['modalAttributes']['blur'] ?? false);
                    $panelWrapClasses = $modalConfig->modalPanelWrapClasses($modal['modalAttributes']);
                    $transitionClasses = $modalConfig->modalTransitionClasses($modal['modalAttributes']);
                    $usesLayout = $modalConfig->usesLayout($modal['modalAttributes']);
                    $layoutHeading = $modalConfig->layoutHeading($modal['modalAttributes']);
                    $layoutDescription = $modalConfig->layoutDescription($modal['modalAttributes']);
                    $layoutShowClose = $modalConfig->layoutShowClose($modal['modalAttributes']);
                    $layoutFooterActionsAlignmentClass = $modalConfig->layoutFooterActionsAlignmentClass($modal['modalAttributes']);
                    $layoutFooterActions = $modalConfig->layoutFooterActions($modal['modalAttributes']);
                @endphp

                <div
                    x-show="shouldShowModal(@js($id))"
                    x-transition.opacity.duration.200ms
                    x-on:click="if (isTopModal(@js($id))) handleClickAway()"
                    x-bind:class="{ 'pointer-events-none': !isTopModal(@js($id)) }"
                    @class([
                        'absolute inset-0 bg-zinc-950/50',
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
                    x-on:click="if ($event.target === $event.currentTarget) handleClickAway()"
                    x-bind:class="{ 'pointer-events-none': !isTopModal(@js($id)) }"
                    style="z-index: {{ 21 + ($loop->index * 2) }};"
                    class="{{ $panelWrapClasses }} {{ $originClass }}"
                    x-ref="{{ $id }}"
                    wire:key="corepine-modal-{{ $id }}"
                >
                    <div @class([
                        'flex min-h-0 w-full flex-col overflow-hidden bg-white dark:bg-zinc-800',
                        'h-[50dvh] max-h-[calc(100dvh-1rem)]' => ! $isDrawer && ! $isSheet,
                        'h-[100dvh] max-h-[100dvh]' => $isDrawer,
                        'mx-auto' => ! $isDrawer && ! in_array($position, ['left', 'right'], true),
                        'rounded-lg' => ! $isDrawer && ! $isSheet,
                        'rounded-r-lg rounded-l-none' => $isDrawer && $position === 'left',
                        'rounded-l-lg rounded-r-none' => $isDrawer && $position === 'right',
                        'rounded-t-2xl rounded-b-none' => $isSheet,
                        $modalClasses,
                        'rounded-l-none' => $isDrawer && $position === 'left',
                        'rounded-r-none' => $isDrawer && $position === 'right',
                        'rounded-b-none' => $isSheet,
                    ])
                        x-ref="panel-{{ $id }}"
                        x-bind:style="panelStyle(@js($id))"
                        x-on:pointerdown.capture="startSheetDrag(@js($id), $event)"
                        x-on:touchstart.capture="startSheetDrag(@js($id), $event)"
                        x-on:mousedown.capture="startSheetDrag(@js($id), $event)"
                        x-on:click.stop
                    >
                        @if ($isSheet)
                            <div
                                x-show="shouldShowSheetDragHandle(@js($id))"
                                class="cursor-row-resize select-none px-4 pt-3 sm:pt-4"
                                x-on:pointerdown.stop.prevent="startSheetResize(@js($id), $event)"
                                x-on:touchstart.stop.prevent="startSheetResize(@js($id), $event)"
                                x-on:mousedown.stop.prevent="startSheetResize(@js($id), $event)"
                                style="touch-action: none;"
                            >
                                <div class="mx-auto h-1.5 w-10 rounded-full bg-zinc-300/80 dark:bg-zinc-600/80"></div>
                            </div>
                        @endif
                        <div class="min-h-0 flex flex-1 dark:text-white [&>*]:min-h-0 [&>*]:flex-1" data-corepine-modal-livewire>
                            @if ($usesLayout)
                                <x-corepine.modal.layout :heading="$layoutHeading" :description="$layoutDescription" :show-close="$layoutShowClose" class="h-full">
                                    @livewire($modal['name'] ?: $modal['class'], $modal['arguments'], key('corepine-modal-panel-'.$id))

                                    @if ($layoutFooterActions !== [])
                                        <x-corepine.modal.footer>
                                            <div class="flex w-full items-center gap-2 {{ $layoutFooterActionsAlignmentClass }}">
                                                @foreach ($layoutFooterActions as $action)
                                                    @php
                                                        $actionClass = is_string($action['class'] ?? null) ? trim((string) $action['class']) : '';
                                                        $actionStyle = is_string($action['style'] ?? null) ? trim((string) $action['style']) : '';
                                                        $actionDisabled = (bool) ($action['disabled'] ?? false);
                                                        $actionVisible = (bool) ($action['visible'] ?? true);
                                                        $actionAttributes = new \Illuminate\View\ComponentAttributeBag(is_array($action['attributes'] ?? null) ? $action['attributes'] : []);

                                                        if (! $actionVisible) {
                                                            continue;
                                                        }

                                                        if ($actionClass !== '') {
                                                            $actionAttributes = $actionAttributes->class($actionClass);
                                                        }

                                                        if ($actionStyle !== '') {
                                                            $actionAttributes = $actionAttributes->merge(['style' => $actionStyle]);
                                                        }
                                                    @endphp

                                                    @if (($action['type'] ?? 'method') === 'close')
                                                        <button
                                                            type="button"
                                                            @if ($actionDisabled) disabled @endif
                                                            {{ $actionAttributes }}
                                                            @if (! $actionDisabled)
                                                                x-on:click.stop="requestClose({
                                                                    count: @js($action['count'] ?? 1),
                                                                    destroy: @js($action['destroy'] ?? true),
                                                                    closeAll: @js($action['closeAll'] ?? false),
                                                                })"
                                                            @endif
                                                        >
                                                            {{ $action['label'] ?? 'Close' }}
                                                        </button>
                                                    @else
                                                        <button
                                                            type="{{ $action['buttonType'] ?? 'button' }}"
                                                            @if ($actionDisabled) disabled @endif
                                                            {{ $actionAttributes }}
                                                            x-on:click.stop="callModalMethod(@js($id), @js($action['method'] ?? ''), @js($action['params'] ?? []))"
                                                        >
                                                            {{ $action['label'] ?? 'Action' }}
                                                        </button>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </x-corepine.modal.footer>
                                    @endif
                                </x-corepine.modal.layout>
                            @else
                                @livewire($modal['name'] ?: $modal['class'], $modal['arguments'], key('corepine-modal-panel-'.$id))
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
