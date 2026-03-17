@props([
    'id' => null,
    'open' => false,
    'title' => null,
    'description' => null,
    'size' => 'default',
    'closeOnEscape' => true,
    'closeOnClickAway' => true,
    'blur' => false,
    'showClose' => true,
])

@php($modalConfig = app(\Corepine\Modal\Support\ModalConfig::class))
@php($resolvedId = is_string($id) && trim($id) !== '' ? trim($id) : null)
@php($resolvedTitle = is_string($title) && trim($title) !== '' ? $title : null)
@php($resolvedDescription = is_string($description) && trim($description) !== '' ? $description : null)
@php($sizeClasses = $modalConfig->modalSizeClasses(['size' => $size]))
@php($extraClass = is_string($attributes->get('class')) ? $attributes->get('class') : '')
@php($hasFooter = isset($footer) && $footer->isNotEmpty())
@php($panelAttributes = $attributes->except('class'))

<div
    x-data="{
        open: @js((bool) $open),
        modalId: @js($resolvedId),
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
    }"
    x-init="$watch('open', (value) => document.body.classList.toggle('cp-modal-open', value)); if (open) document.body.classList.add('cp-modal-open');"
    x-on:corepine-modal:open.window="if (matches($event.detail ?? {})) open = true"
    x-on:corepine-modal:close.window="if (matches($event.detail ?? {})) open = false"
    x-on:corepine-modal:toggle.window="if (matches($event.detail ?? {})) open = !open"
    x-on:keydown.escape.window.stop="if (@js((bool) $closeOnEscape) && open) open = false"
    x-cloak
    x-show="open"
    class="cp-modal fixed inset-0 z-[999] overflow-y-auto"
    style="display: none;"
    role="dialog"
    aria-modal="true"
    data-corepine-modal-id="{{ $resolvedId }}"
>
    <div class="cp-modal-viewport relative min-h-full">
        <div
            x-show="open"
            x-transition.opacity.duration.200ms
            x-on:click="if (@js((bool) $closeOnClickAway)) open = false"
            @class([
                'cp-modal-layer-backdrop absolute inset-0 bg-zinc-950/50',
                'backdrop-blur-sm' => (bool) $blur,
            ])
        ></div>

        <div
            x-show="open"
            x-transition:enter="duration-200 ease-out"
            x-transition:enter-start="opacity-0 translate-y-6 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="duration-150 ease-in"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
            x-on:click="if (@js((bool) $closeOnClickAway)) open = false"
            class="cp-modal-panel-wrap absolute inset-0 flex w-full items-center justify-center p-4 sm:p-8"
        >
            <section
                {{ $panelAttributes->merge(['class' => trim("cp-modal-component cp-modal-shape-default w-full overflow-hidden bg-white text-zinc-900 shadow-xl dark:bg-zinc-800 dark:text-zinc-100 {$sizeClasses} {$extraClass}")]) }}
                x-on:click.stop
            >
                @if ($resolvedTitle !== null || $resolvedDescription !== null || $showClose)
                    <header class="cp-modal-header flex items-start justify-between gap-3 border-b border-zinc-200/70 px-5 py-4 dark:border-zinc-700/70">
                        <div class="min-w-0">
                            @if ($resolvedTitle !== null)
                                <h2 class="cp-modal-title text-base font-semibold leading-none text-zinc-900 dark:text-zinc-100">
                                    {{ $resolvedTitle }}
                                </h2>
                            @endif

                            @if ($resolvedDescription !== null)
                                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $resolvedDescription }}
                                </p>
                            @endif
                        </div>

                        @if ($showClose)
                            <button
                                type="button"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-zinc-700 dark:hover:text-zinc-100"
                                x-on:click="open = false"
                            >
                                <span class="sr-only">Close</span>
                                <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" aria-hidden="true">
                                    <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
                                </svg>
                            </button>
                        @endif
                    </header>
                @endif

                <main @class([
                    'cp-modal-body overflow-y-auto px-5 py-4',
                    'max-h-[78dvh]' => ! $hasFooter,
                    'max-h-[70dvh]' => $hasFooter,
                ])>
                    {{ $slot }}
                </main>

                @if ($hasFooter)
                    <footer {{ $footer->attributes->class('cp-modal-footer flex items-center border-t border-zinc-200/70 px-5 py-3 dark:border-zinc-700/70') }}>
                        {{ $footer }}
                    </footer>
                @endif
            </section>
        </div>
    </div>
</div>
