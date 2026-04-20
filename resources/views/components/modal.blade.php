@props([
    'id' => null,
    'open' => false,
    'heading' => null,
    'description' => null,
    'showClose' => null,
    'modalAttributes' => [],
    'size' => 'default',
    'type' => null,
    'drawer' => null,
    'sheet' => null,
    'bottomSheet' => null,
    'placement' => null,
    'origin' => null,
    'height' => null,
    'maxHeight' => null,
    'draggable' => null,
    'showDragHandle' => null,
    'dragCloseThreshold' => null,
    'closeOnEscape' => true,
    'closeAllOnEscape' => false,
    'dismissible' => null,
    'blur' => false,
    'dispatch' => null,
    'dispatchTo' => null,
])

@php($modalConfig = app(\Corepine\Modal\Support\ModalConfig::class))
@php($resolvedId = is_string($id) && trim($id) !== '' ? trim($id) : null)
@php($resolvedHeading = is_string($heading) && trim($heading) !== '' ? $heading : null)
@php($resolvedDescription = is_string($description) && trim($description) !== '' ? $description : null)
@php($hasHeaderSlot = isset($header))
@php($namedHeader = $hasHeaderSlot ? $header : null)
@php($hasFooter = isset($footer) && $footer->isNotEmpty())
@php($panelAttributes = $attributes->except('class'))
@php($normalizeBoolean = static function (mixed $value, ?bool $fallback = null): ?bool {
    if (is_bool($value)) {
        return $value;
    }

    if (is_string($value)) {
        return match (strtolower(trim($value))) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => $fallback,
        };
    }

    if (is_int($value) || is_float($value)) {
        if ((float) $value === 1.0) {
            return true;
        }

        if ((float) $value === 0.0) {
            return false;
        }
    }

    return $fallback;
})
@php($payloadModalAttributes = is_array($modalAttributes) ? $modalAttributes : [])
@php($normalizedType = null)
@if ($type instanceof \Corepine\Modal\Enums\ModalType)
    @php($normalizedType = $type->value)
@elseif (is_string($type))
    @php($normalizedType = match (strtolower(trim($type))) {
        'bottomsheet', 'bottom-sheet', 'bottom_sheet' => 'sheet',
        'modal', 'drawer', 'sheet' => strtolower(trim($type)),
        default => null,
    })
@endif
@php($normalizedDrawer = ! is_null($drawer) ? $normalizeBoolean($drawer, null) : null)
@php($normalizedSheet = ! is_null($sheet) ? $normalizeBoolean($sheet, null) : null)
@php($normalizedBottomSheet = ! is_null($bottomSheet) ? $normalizeBoolean($bottomSheet, null) : null)
@if (! is_null($normalizedDrawer))
    @php($payloadModalAttributes['drawer'] = $normalizedDrawer)
    @if (is_null($normalizedType) && $normalizedDrawer === true)
        @php($normalizedType = 'drawer')
    @endif
@endif
@if (! is_null($normalizedSheet))
    @php($payloadModalAttributes['sheet'] = $normalizedSheet)
    @if (is_null($normalizedType) && $normalizedSheet === true)
        @php($normalizedType = 'sheet')
    @endif
@endif
@if (! is_null($normalizedBottomSheet))
    @php($payloadModalAttributes['bottomSheet'] = $normalizedBottomSheet)
    @if (is_null($normalizedType) && $normalizedBottomSheet === true)
        @php($normalizedType = 'sheet')
    @endif
@endif
@if (! is_null($normalizedType))
    @php($payloadModalAttributes['type'] = $normalizedType)
@endif
@if (is_string($placement) && trim($placement) !== '')
    @php($payloadModalAttributes['placement'] = strtolower(trim($placement)))
@endif
@if (is_string($origin) && trim($origin) !== '')
    @php($payloadModalAttributes['origin'] = strtolower(trim($origin)))
@endif
@if (is_string($size) && trim($size) !== '')
    @php($payloadModalAttributes['size'] = trim($size))
@endif
@if (is_int($height) || is_float($height) || (is_string($height) && trim($height) !== ''))
    @php($payloadModalAttributes['height'] = $height)
@endif
@if (is_int($maxHeight) || is_float($maxHeight) || (is_string($maxHeight) && trim($maxHeight) !== ''))
    @php($payloadModalAttributes['maxHeight'] = $maxHeight)
@endif
@if (is_int($dragCloseThreshold) || is_float($dragCloseThreshold) || (is_string($dragCloseThreshold) && trim($dragCloseThreshold) !== ''))
    @php($payloadModalAttributes['dragCloseThreshold'] = $dragCloseThreshold)
@endif
@php($normalizedBlur = $normalizeBoolean($blur, null))
@if (! is_null($normalizedBlur))
    @php($payloadModalAttributes['blur'] = $normalizedBlur)
@endif
@php($normalizedCloseOnEscape = $normalizeBoolean($closeOnEscape, null))
@if (! is_null($normalizedCloseOnEscape))
    @php($payloadModalAttributes['closeOnEscape'] = $normalizedCloseOnEscape)
@endif
@php($resolvedShowClose = ! is_null($showClose)
    ? $normalizeBoolean($showClose, true)
    : ($resolvedHeading !== null || $resolvedDescription !== null))
@php($attributeMap = method_exists($attributes, 'getAttributes') ? $attributes->getAttributes() : iterator_to_array($attributes))
@php($hasSubmitAttribute = collect(array_keys($attributeMap))->contains(
    static fn (string $key): bool => str_starts_with($key, 'wire:submit')
        || str_starts_with($key, 'x-on:submit')
        || str_starts_with($key, '@submit')
))
@php($isFormPanel = $hasSubmitAttribute)
@php($panelElement = $isFormPanel ? 'form' : 'section')
@php($submittedMethod = is_string($attributes->get('method')) ? strtolower(trim((string) $attributes->get('method'))) : null)
@php($formMethod = $submittedMethod)
@if ($isFormPanel)
    @php($formMethod = in_array($formMethod, ['get', 'post', 'put', 'patch', 'delete'], true) ? $formMethod : 'post')
@endif
@php($panelElementAttributes = $isFormPanel ? $panelAttributes->except('method')->merge([
    'method' => in_array($formMethod, ['put', 'patch', 'delete'], true) ? 'post' : $formMethod,
]) : $panelAttributes)
@php($normalizedCloseAllOnEscape = $normalizeBoolean($closeAllOnEscape, null))
@if (! is_null($normalizedCloseAllOnEscape))
    @php($payloadModalAttributes['closeAllOnEscape'] = $normalizedCloseAllOnEscape)
@endif
@php($normalizedDismissible = $normalizeBoolean($dismissible, null))
@if (! is_null($normalizedDismissible))
    @php($payloadModalAttributes['dismissible'] = $normalizedDismissible)
@endif
@php($normalizedDraggable = ! is_null($draggable) ? $normalizeBoolean($draggable, true) : null)
@if (! is_null($normalizedDraggable))
    @php($payloadModalAttributes['draggable'] = $normalizedDraggable)
@endif
@php($normalizedShowDragHandle = ! is_null($showDragHandle) ? $normalizeBoolean($showDragHandle, true) : null)
@if (! is_null($normalizedShowDragHandle))
    @php($payloadModalAttributes['showDragHandle'] = $normalizedShowDragHandle)
@endif
@if (is_array($dispatch) && $dispatch !== [])
    @php($payloadModalAttributes['dispatch'] = $dispatch)
@endif
@if (is_array($dispatchTo) && $dispatchTo !== [])
    @php($payloadModalAttributes['dispatchTo'] = $dispatchTo)
@endif
@php($existingClass = isset($payloadModalAttributes['class']) && is_string($payloadModalAttributes['class']) ? $payloadModalAttributes['class'] : '')
@php($incomingClass = is_string($attributes->get('class')) ? $attributes->get('class') : '')
@php($mergedClass = trim(implode(' ', array_filter([$existingClass, $incomingClass]))))
@if ($mergedClass !== '')
    @php($payloadModalAttributes['class'] = $mergedClass)
@endif
@php($resolvedModalAttributes = $modalConfig->mergedModalAttributes([], $payloadModalAttributes))
@php($isDrawer = $modalConfig->isDrawer($resolvedModalAttributes))
@php($isSheet = $modalConfig->isSheet($resolvedModalAttributes))
@php($placement = $modalConfig->modalPlacement($resolvedModalAttributes))
@php($originClass = $modalConfig->modalOriginClass($resolvedModalAttributes))
@php($hasBlur = (bool) ($resolvedModalAttributes['blur'] ?? false))
@php($modalClasses = $modalConfig->mergedModalClasses($resolvedModalAttributes))
@php($panelWrapClasses = $modalConfig->modalPanelWrapClasses($resolvedModalAttributes))
@php($transitionClasses = $modalConfig->modalTransitionClasses($resolvedModalAttributes))
@php($standaloneOptions = [
    'id' => $resolvedId,
    'open' => (bool) $open,
    'type' => $modalConfig->modalType($resolvedModalAttributes),
    'closeOnEscape' => (bool) ($resolvedModalAttributes['closeOnEscape'] ?? true),
    'closeAllOnEscape' => (bool) ($resolvedModalAttributes['closeAllOnEscape'] ?? false),
    'dismissible' => (bool) ($resolvedModalAttributes['dismissible'] ?? true),
    'draggable' => (bool) ($resolvedModalAttributes['draggable'] ?? $isSheet),
    'showDragHandle' => (bool) ($resolvedModalAttributes['showDragHandle'] ?? ($resolvedModalAttributes['draggable'] ?? $isSheet)),
    'dragCloseThreshold' => $resolvedModalAttributes['dragCloseThreshold'] ?? 0.3,
    'height' => $resolvedModalAttributes['height'] ?? null,
    'maxHeight' => $resolvedModalAttributes['maxHeight'] ?? null,
    'panelClass' => is_string($resolvedModalAttributes['class'] ?? null) ? $resolvedModalAttributes['class'] : '',
    'dispatch' => is_array($resolvedModalAttributes['dispatch'] ?? null) ? $resolvedModalAttributes['dispatch'] : [],
    'dispatchTo' => is_array($resolvedModalAttributes['dispatchTo'] ?? null) ? $resolvedModalAttributes['dispatchTo'] : [],
])

@if (isset($__livewire))
    @script
@endif
    <script>
        if (typeof window.corepineStandaloneModal !== 'function') {
            window.corepineStandaloneModal = (options = {}) => ({
            open: Boolean(options.open ?? false),
            modalId: options.id ?? null,
            eventNames: {
                open: @js($modalConfig->listenEvent('open')),
                close: @js($modalConfig->listenEvent('close')),
                toggle: @js($modalConfig->listenEvent('toggle')),
            },
            windowListeners: [],
            livewireListeners: [],
            type: options.type ?? 'modal',
            closeOnEscape: options.closeOnEscape !== false,
            closeAllOnEscape: options.closeAllOnEscape === true,
            dismissible: options.dismissible !== false,
            draggable: options.draggable !== false,
            showDragHandle: options.showDragHandle !== false,
            dragCloseThresholdValue: options.dragCloseThreshold ?? 0.3,
            heightValue: options.height ?? null,
            maxHeightValue: options.maxHeight ?? null,
            panelClassValue: options.panelClass ?? '',
            closeDispatch: options.dispatch ?? {},
            closeDispatchTo: options.dispatchTo ?? {},
            defaultSheetHeightRatio: 0.7,
            defaultSheetTopGap: 16,
            defaultSheetMinHeight: 260,
            sheetHeight: null,
            sheetDragStartY: 0,
            sheetDragOffsetY: 0,
            sheetDragPointerId: null,
            draggingSheet: false,
            resizingSheet: false,
            sheetResizeStartY: 0,
            sheetResizeStartHeight: 0,
            sheetResizePointerId: null,
            closingFromDrag: false,
            closeResetTimer: null,

            init() {
                this.syncBodyClass();
                this.registerEventListeners();

                if (this.open && this.isSheet()) {
                    this.ensureSheetHeight();
                }

                this.$watch('open', (value) => {
                    if (value && this.isSheet()) {
                        this.ensureSheetHeight();
                        this.resetSheetCloseState();
                    }

                    if (!value && !this.closingFromDrag) {
                        this.clearSheetDrag();
                        this.clearSheetResize();
                    }

                    this.syncBodyClass();
                });
            },

            destroy() {
                this.windowListeners.forEach(([eventName, listener]) => {
                    window.removeEventListener(eventName, listener);
                });
                this.windowListeners = [];

                this.livewireListeners.forEach((listener) => {
                    if (typeof listener === 'function') {
                        listener();
                    }
                });
                this.livewireListeners = [];

                this.resetSheetCloseState();
                this.clearSheetDrag();
                this.clearSheetResize();
                this.syncBodyClass();
            },

            registerEventListeners() {
                this.registerWindowListener(this.eventNames.open, (payload = {}) => this.openFromEvent(payload));
                this.registerWindowListener(this.eventNames.close, (payload = {}) => this.closeFromEvent(payload));
                this.registerWindowListener(this.eventNames.toggle, (payload = {}) => this.toggleFromEvent(payload));

                if (typeof Livewire?.on === 'function') {
                    this.livewireListeners.push(
                        Livewire.on(this.eventNames.open, (payload = {}) => this.openFromEvent(payload))
                    );
                    this.livewireListeners.push(
                        Livewire.on(this.eventNames.close, (payload = {}) => this.closeFromEvent(payload))
                    );
                    this.livewireListeners.push(
                        Livewire.on(this.eventNames.toggle, (payload = {}) => this.toggleFromEvent(payload))
                    );
                }
            },

            registerWindowListener(eventName, callback) {
                const listener = (event) => callback(event?.detail ?? {});

                window.addEventListener(eventName, listener);
                this.windowListeners.push([eventName, listener]);
            },

            isSheet() {
                return this.type === 'sheet';
            },

            isDrawer() {
                return this.type === 'drawer';
            },

            isDismissible() {
                return this.dismissible !== false;
            },

            shouldCloseAllOnEscape() {
                return this.closeAllOnEscape === true;
            },

            isSheetDraggable() {
                return this.isSheet() && this.draggable !== false;
            },

            shouldShowSheetDragHandle() {
                return this.isSheetDraggable() && this.showDragHandle !== false;
            },

            matches(payload = {}) {
                const target = payload?.id ?? payload?.name ?? null;

                if (target === null || target === '') {
                    return this.modalId === null;
                }

                if (this.modalId === null) {
                    return false;
                }

                return String(target) === String(this.modalId);
            },

            openFromEvent(payload = {}) {
                if (!this.matches(payload)) {
                    return;
                }

                this.resetSheetCloseState();
                this.open = true;

                if (this.isSheet()) {
                    this.ensureSheetHeight();
                }
            },

            closeFromEvent(payload = {}) {
                if (!this.matches(payload)) {
                    return;
                }

                this.close(payload);
            },

            toggleFromEvent(payload = {}) {
                if (!this.matches(payload)) {
                    return;
                }

                if (this.open) {
                    this.close(payload);

                    return;
                }

                this.openFromEvent(payload);
            },

            close(payload = {}) {
                this.open = false;

                if (!this.closingFromDrag) {
                    this.clearSheetDrag();
                    this.clearSheetResize();
                }

                this.dispatchCloseEvents(payload);
            },

            handleEscape() {
                if (!this.open || !this.closeOnEscape) {
                    return;
                }

                this.close();
            },

            handleClickAway() {
                if (!this.isDismissible()) {
                    return;
                }

                this.close();
            },

            syncBodyClass() {
                const openStandaloneCount = document.querySelectorAll('[data-corepine-standalone="true"][data-open="true"]').length;

                if (openStandaloneCount > 0) {
                    document.body.classList.add('overflow-hidden');

                    return;
                }

                document.body.classList.remove('overflow-hidden');
            },

            normalizeDispatchMap(value) {
                if (!value || typeof value !== 'object' || Array.isArray(value)) {
                    return {};
                }

                return Object.entries(value).reduce((carry, [eventName, params]) => {
                    if (typeof eventName !== 'string' || eventName.trim() === '') {
                        return carry;
                    }

                    if (params && typeof params === 'object' && !Array.isArray(params)) {
                        carry[eventName.trim()] = params;
                    } else if (Array.isArray(params)) {
                        carry[eventName.trim()] = params;
                    } else if (params == null) {
                        carry[eventName.trim()] = {};
                    } else {
                        carry[eventName.trim()] = [params];
                    }

                    return carry;
                }, {});
            },

            normalizeDispatchTargets(value) {
                if (!value || typeof value !== 'object' || Array.isArray(value)) {
                    return {};
                }

                return Object.entries(value).reduce((carry, [component, events]) => {
                    if (typeof component !== 'string' || component.trim() === '') {
                        return carry;
                    }

                    const normalizedEvents = this.normalizeDispatchMap(events);

                    if (Object.keys(normalizedEvents).length > 0) {
                        carry[component.trim()] = normalizedEvents;
                    }

                    return carry;
                }, {});
            },

            mergedCloseDispatch(payload = {}) {
                return {
                    ...this.normalizeDispatchMap(this.closeDispatch),
                    ...this.normalizeDispatchMap(payload.dispatch ?? {}),
                };
            },

            mergedCloseDispatchTo(payload = {}) {
                const merged = {
                    ...this.normalizeDispatchTargets(this.closeDispatchTo),
                };

                Object.entries(this.normalizeDispatchTargets(payload.dispatchTo ?? {})).forEach(([component, events]) => {
                    merged[component] = {
                        ...(merged[component] ?? {}),
                        ...events,
                    };
                });

                return merged;
            },

            dispatchCloseEvents(payload = {}) {
                Object.entries(this.mergedCloseDispatch(payload)).forEach(([eventName, params]) => {
                    if (typeof Livewire?.dispatch === 'function') {
                        Livewire.dispatch(eventName, params);

                        return;
                    }

                    window.dispatchEvent(new CustomEvent(eventName, {
                        detail: params,
                    }));
                });

                if (typeof Livewire?.dispatchTo !== 'function') {
                    return;
                }

                Object.entries(this.mergedCloseDispatchTo(payload)).forEach(([component, events]) => {
                    Object.entries(events).forEach(([eventName, params]) => {
                        Livewire.dispatchTo(component, eventName, params);
                    });
                });
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

                    if (!Number.isNaN(ratio)) {
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

            handleViewportResize() {
                if (this.isSheet() && this.sheetHeight !== null) {
                    this.sheetHeight = this.clampSheetHeight(this.sheetHeight);
                }
            },

            sheetMinHeight() {
                return Math.max(120, Math.round(this.defaultSheetMinHeight));
            },

            sheetMaxHeight() {
                const viewport = this.viewportHeight();
                const fallback = viewport - this.defaultSheetTopGap;
                const configured = this.normalizeHeightValue(this.maxHeightValue, fallback);
                const max = Math.min(viewport, Math.round(configured ?? fallback));

                return Math.max(this.sheetMinHeight(), max);
            },

            clampSheetHeight(height) {
                const min = this.sheetMinHeight();
                const max = this.sheetMaxHeight();

                return Math.max(min, Math.min(max, Math.round(height)));
            },

            resolveInitialSheetHeight() {
                const fallback = this.viewportHeight() * this.defaultSheetHeightRatio;
                const classPreferred = this.classHeightHint(this.panelClassValue);
                const preferredSource = this.heightValue ?? null;
                const normalizedPreferredSource = this.classHeightHint(
                    typeof preferredSource === 'string' ? preferredSource : ''
                ) ?? preferredSource;
                const preferred = this.normalizeHeightValue(
                    normalizedPreferredSource ?? classPreferred,
                    fallback
                );

                return this.clampSheetHeight(preferred ?? fallback);
            },

            ensureSheetHeight() {
                if (!this.isSheet()) {
                    return;
                }

                if (this.sheetHeight === null) {
                    this.sheetHeight = this.resolveInitialSheetHeight();
                } else {
                    this.sheetHeight = this.clampSheetHeight(this.sheetHeight);
                }
            },

            resolvedDragThreshold() {
                const raw = Number.parseFloat(this.dragCloseThresholdValue ?? 0.3);

                if (Number.isNaN(raw)) {
                    return 0.3;
                }

                return Math.min(0.95, Math.max(0.05, raw));
            },

            panelStyle() {
                if (this.isSheet()) {
                    this.ensureSheetHeight();

                    const offset = (this.draggingSheet || this.closingFromDrag) ? this.sheetDragOffsetY : 0;
                    const transition = (this.draggingSheet || this.resizingSheet)
                        ? 'none'
                        : 'transform 180ms ease-out, height 140ms ease-out';
                    const height = this.sheetHeight ?? this.resolveInitialSheetHeight();
                    const maxHeight = this.sheetMaxHeight();

                    return `height: ${height}px; max-height: ${maxHeight}px; transform: translate3d(0, ${offset}px, 0); transition: ${transition};`;
                }

                const explicitHeight = this.normalizePanelHeightValue(this.heightValue, null);
                const explicitMaxHeight = this.normalizePanelHeightValue(this.maxHeightValue, null);

                if (!explicitHeight && !explicitMaxHeight) {
                    return '';
                }

                const styles = [];

                if (explicitHeight) {
                    styles.push(`height: ${explicitHeight}`);
                }

                if (explicitMaxHeight) {
                    styles.push(`max-height: ${explicitMaxHeight}`);
                } else if (this.isDrawer() && explicitHeight) {
                    styles.push('max-height: 100dvh');
                }

                return `${styles.join('; ')};`;
            },

            startSheetDrag(event) {
                if (!this.isSheetDraggable() || !this.open || this.resizingSheet) {
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

                this.draggingSheet = true;
                this.sheetDragStartY = startY;
                this.sheetDragOffsetY = 0;
                this.sheetDragPointerId = event?.pointerId ?? null;
            },

            moveSheetDrag(event) {
                if (this.resizingSheet) {
                    this.moveSheetResize(event);

                    return;
                }

                if (!this.draggingSheet) {
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

            clearSheetDrag(resetOffset = true) {
                this.draggingSheet = false;
                this.sheetDragStartY = 0;
                this.sheetDragPointerId = null;

                if (resetOffset) {
                    this.sheetDragOffsetY = 0;
                }
            },

            startSheetResize(event) {
                if (!this.isSheetDraggable() || !this.open || this.draggingSheet) {
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

                this.ensureSheetHeight();
                this.resizingSheet = true;
                this.sheetResizeStartY = startY;
                this.sheetResizeStartHeight = this.sheetHeight ?? this.resolveInitialSheetHeight();
                this.sheetResizePointerId = event?.pointerId ?? null;

                if (event?.cancelable) {
                    event.preventDefault();
                }
            },

            moveSheetResize(event) {
                if (!this.resizingSheet) {
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
                this.sheetHeight = this.clampSheetHeight(nextHeight);

                if (event?.cancelable) {
                    event.preventDefault();
                }
            },

            clearSheetResize() {
                this.resizingSheet = false;
                this.sheetResizeStartY = 0;
                this.sheetResizeStartHeight = 0;
                this.sheetResizePointerId = null;
            },

            endSheetResize(event) {
                if (!this.resizingSheet) {
                    return;
                }

                if (this.sheetResizePointerId !== null && event?.pointerId !== undefined && event.pointerId !== this.sheetResizePointerId) {
                    return;
                }

                this.clearSheetResize();
            },

            endSheetDrag(event) {
                if (this.resizingSheet) {
                    this.endSheetResize(event);

                    return;
                }

                if (!this.draggingSheet) {
                    return;
                }

                if (this.sheetDragPointerId !== null && event?.pointerId !== undefined && event.pointerId !== this.sheetDragPointerId) {
                    return;
                }

                const releaseY = this.eventClientY(event);

                if (releaseY !== null) {
                    const releaseOffset = Math.max(0, releaseY - this.sheetDragStartY);
                    this.sheetDragOffsetY = Math.max(this.sheetDragOffsetY, releaseOffset);
                }

                const finalOffset = this.sheetDragOffsetY;
                const panel = this.$refs.panel;
                const panelHeight = panel?.offsetHeight ?? this.viewportHeight();
                const threshold = panelHeight * this.resolvedDragThreshold();
                const shouldClose = finalOffset >= threshold;

                if (!shouldClose) {
                    this.clearSheetDrag();

                    return;
                }

                this.resetSheetCloseState();
                this.closingFromDrag = true;
                this.sheetDragOffsetY = Math.max(finalOffset, panelHeight);
                this.sheetDragStartY = 0;
                this.sheetDragPointerId = null;
                this.draggingSheet = false;
                this.open = false;

                this.closeResetTimer = setTimeout(() => {
                    this.resetSheetCloseState();
                    this.clearSheetDrag();
                    this.clearSheetResize();
                    this.syncBodyClass();
                }, 260);
            },

            resetSheetCloseState() {
                if (this.closeResetTimer) {
                    clearTimeout(this.closeResetTimer);
                    this.closeResetTimer = null;
                }

                this.closingFromDrag = false;
            },
            });

            // Ensure global identifier access for Alpine expression evaluation.
            if (typeof globalThis !== 'undefined') {
                globalThis.corepineStandaloneModal = window.corepineStandaloneModal;
            }
        }
    </script>
@if (isset($__livewire))
    @endscript
@endif

<div
    x-data="window.corepineStandaloneModal(@js($standaloneOptions))"
    x-init="init()"
    x-on:keydown.escape.window.stop="handleEscape()"
    x-on:pointermove.window="moveSheetDrag($event)"
    x-on:pointerup.window="endSheetDrag($event)"
    x-on:pointercancel.window="endSheetDrag($event)"
    x-on:touchmove.window="moveSheetDrag($event)"
    x-on:touchend.window="endSheetDrag($event)"
    x-on:touchcancel.window="endSheetDrag($event)"
    x-on:mousemove.window="moveSheetDrag($event)"
    x-on:mouseup.window="endSheetDrag($event)"
    x-on:resize.window.debounce.120ms="handleViewportResize()"
    x-bind:data-open="open ? 'true' : 'false'"
    data-corepine-standalone="true"
    data-corepine-modal-id="{{ $resolvedId }}"
>
        <template x-teleport="body">
            <div
                x-cloak
                x-show="open"
                class="fixed inset-0 z-[999] overflow-y-auto"
                style="display: none;"
                role="dialog"
                aria-modal="true"
                data-corepine-modal-id="{{ $resolvedId }}"
            >
                <div class="relative min-h-full">
                    <div
                        x-show="open"
                        x-transition.opacity.duration.200ms
                        x-on:click="handleClickAway()"
                        @class([
                            'absolute inset-0 bg-zinc-950/50',
                            'backdrop-blur-sm' => $hasBlur,
                        ])
                    ></div>

                    <div
                        x-show="open"
                        x-transition:enter="{{ $transitionClasses['enter'] }}"
                        x-transition:enter-start="{{ $transitionClasses['enterStart'] }}"
                        x-transition:enter-end="{{ $transitionClasses['enterEnd'] }}"
                        x-transition:leave="{{ $transitionClasses['leave'] }}"
                        x-transition:leave-start="{{ $transitionClasses['leaveStart'] }}"
                        x-transition:leave-end="{{ $transitionClasses['leaveEnd'] }}"
                        x-on:click="if ($event.target === $event.currentTarget) handleClickAway()"
                        class="{{ $panelWrapClasses }} {{ $originClass }}"
                    >
                        <{{ $panelElement }}
                            x-ref="panel"
                            x-bind:style="panelStyle()"
                            x-on:pointerdown.capture="startSheetDrag($event)"
                            x-on:touchstart.capture="startSheetDrag($event)"
                            x-on:mousedown.capture="startSheetDrag($event)"
                            x-on:click.stop
                            @class([
                                'flex w-full flex-col overflow-hidden bg-white text-zinc-900 shadow-xl dark:bg-zinc-800 dark:text-zinc-100',
                                'h-[50dvh] max-h-[calc(100dvh-1rem)]' => ! $isDrawer && ! $isSheet,
                                'h-[100dvh] max-h-[100dvh]' => $isDrawer,
                                'rounded-lg' => ! $isDrawer && ! $isSheet,
                                'rounded-r-lg rounded-l-none' => $isDrawer && $placement === 'left',
                                'rounded-l-lg rounded-r-none' => $isDrawer && $placement === 'right',
                                'rounded-t-2xl rounded-b-none' => $isSheet,
                                $modalClasses,
                                'rounded-l-none' => $isDrawer && $placement === 'left',
                                'rounded-r-none' => $isDrawer && $placement === 'right',
                                'rounded-b-none' => $isSheet,
                            ])
                            {{ $panelElementAttributes }}
                        >
                            @if ($isFormPanel && $formMethod !== 'get')
                                {!! csrf_field() !!}
                            @endif

                            @if ($isFormPanel && in_array($formMethod, ['put', 'patch', 'delete'], true))
                                {!! method_field(strtoupper($formMethod)) !!}
                            @endif

                            @if ($isSheet)
                                <div
                                    x-show="shouldShowSheetDragHandle()"
                                    class="cursor-row-resize select-none px-4 pt-3 sm:pt-4"
                                    x-on:pointerdown.stop.prevent="startSheetResize($event)"
                                    x-on:touchstart.stop.prevent="startSheetResize($event)"
                                    x-on:mousedown.stop.prevent="startSheetResize($event)"
                                    style="touch-action: none;"
                                >
                                    <div class="mx-auto h-1.5 w-10 rounded-full bg-zinc-300/80 dark:bg-zinc-600/80"></div>
                                </div>
                            @endif

                            @if ($namedHeader !== null || $resolvedHeading !== null || $resolvedDescription !== null || $resolvedShowClose)
                                <header
                                    @if ($namedHeader !== null)
                                        {{ $namedHeader->attributes->class('flex items-start justify-between gap-3 border-b border-zinc-200/70 px-5 py-4 dark:border-zinc-700/70') }}
                                    @else
                                        class="flex items-start justify-between gap-3 border-b border-zinc-200/70 px-5 py-4 dark:border-zinc-700/70"
                                    @endif
                                >
                                    @if ($namedHeader !== null)
                                
                                            {{ $namedHeader }}
                       
                                    @else
                                        <div class="min-w-0">
                                            @if ($resolvedHeading !== null)
                                                <h2 class="text-base font-semibold leading-none text-zinc-900 dark:text-zinc-100">
                                                    {{ $resolvedHeading }}
                                                </h2>
                                            @endif

                                            @if ($resolvedDescription !== null)
                                                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $resolvedDescription }}
                                                </p>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($resolvedShowClose && $namedHeader === null)
                                        <button
                                            type="button"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-zinc-700 dark:hover:text-zinc-100"
                                            x-on:click="close()"
                                        >
                                            <span class="sr-only">Close</span>
                                            <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" aria-hidden="true">
                                                <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
                                            </svg>
                                        </button>
                                    @endif
                                </header>
                            @endif

                            <main class="grow overflow-y-auto px-5 py-4">
                                {{ $slot }}
                            </main>

                            @if ($hasFooter)
                                <footer {{ $footer->attributes->class('flex items-center justify-end  border-zinc-200/70 px-5 py-2 dark:border-zinc-700/70') }}>
                                    {{ $footer }}
                                </footer>
                            @endif
                        </{{ $panelElement }}>
                    </div>
                </div>
            </div>
        </template>
</div>
