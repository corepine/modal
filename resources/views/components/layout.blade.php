@props([
    'title' => null,
    'showClose' => true,
])

@php($resolvedTitle = is_string($title) && trim($title) !== '' ? $title : null)
@php($hasFooter = isset($footer) && $footer->isNotEmpty())

<section {{ $attributes->merge(['class' => 'cp-modal-layout dark:bg-zinc-800 dark:text-white max-h-full flex flex-col overflow-hidden bg-inherit']) }}>
    @if ($resolvedTitle !== null || $showClose)
        <header class="cp-modal-header flex items-center sticky top-0 justify-between gap-3 border-b border-zinc-200/70 px-5 py-4 dark:border-zinc-700/70">
            @if ($resolvedTitle !== null)
                <h2 class="cp-modal-title text-base font-semibold leading-none text-zinc-900 dark:text-zinc-100">
                    {{ $resolvedTitle }}
                </h2>
            @else
                <div></div>
            @endif

            @if ($showClose)
                <x-corepine-modal-close
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                >
                    <span class="sr-only">Close</span>
                    <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" aria-hidden="true">
                        <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
                    </svg>
                </x-corepine-modal-close>
            @endif
        </header>
    @endif

    <main 
         @class([
            'cp-modal-body  grow  overscroll-contain overflow-y-auto px-5 py-4',
            'h-[100%]'=> !$hasFooter,
            'h-[calc(100vh-7.6rem)]'=> $hasFooter,
            ])>
        {{ $slot }}
    </main>

    @if ($hasFooter)
        <footer {{ $footer->attributes->class('cp-modal-footer sticky bottom-0 flex shrink-0 items-center border-t border-zinc-200/70 px-5 py-2 dark:border-zinc-700/70') }}>
            {{ $footer }}
        </footer>
    @endif
</section>
