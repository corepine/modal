@props([
    'title' => null,
    'showClose' => true,
])

@php($resolvedTitle = is_string($title) && trim($title) !== '' ? $title : null)
@php($hasFooter = isset($footer) && trim((string) $footer) !== '')
@php($layoutClasses = 'cp-modal-layout grid min-h-0 overflow-hidden bg-inherit '.($hasFooter ? 'grid-rows-[auto_minmax(0,1fr)_auto] max-h-[90vh]' : 'grid-rows-[auto_minmax(0,1fr)] max-h-[96vh]'))

<section {{ $attributes->merge(['class' => $layoutClasses]) }}>
    @if ($resolvedTitle !== null || $showClose)
        <header class="cp-modal-header flex items-center justify-between gap-3 border-b border-zinc-200/70 px-5 py-4 dark:border-zinc-700/70">
            @if ($resolvedTitle !== null)
                <h2 class="cp-modal-title text-base font-semibold leading-none text-zinc-900 dark:text-zinc-100">
                    {{ $resolvedTitle }}
                </h2>
            @else
                <div></div>
            @endif

            @if ($showClose)
                <x-corepine-close-modal
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                >
                    <span class="sr-only">Close</span>
                    <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" aria-hidden="true">
                        <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
                    </svg>
                </x-corepine-close-modal>
            @endif
        </header>
    @endif

    <main class="cp-modal-body min-h-0 overflow-y-auto px-5 py-4">
        {{ $slot }}
    </main>

    @if ($hasFooter)
        <footer {{ $footer->attributes->class([
            'cp-modal-footer',
            'flex',
            'h-16',
            'shrink-0',
            'items-center',
            'border-t',
            'border-zinc-200/70',
            'px-5',
            'py-2',
            'dark:border-zinc-700/70',
        ]) }}>
            {{ $footer }}
        </footer>
    @endif
</section>
