@props([
    'title' => null,
    'description' => null,
    'showClose' => true,
])

@php($resolvedTitle = is_string($title) && trim($title) !== '' ? $title : null)
@php($hasFooter = isset($footer) && $footer->isNotEmpty())

<section {{ $attributes->merge(['class' => 'cp-modal-layout overscroll-contain dark:bg-zinc-800 dark:text-white h-full max-h-full min-h-0 flex flex-col overflow-hidden bg-inherit']) }}>
    @if ($resolvedTitle !== null || filled($description) || $showClose)
        <header class="cp-modal-header flex shrink-0 items-center justify-between gap-3 border-b border-zinc-200/70 px-5 py-4 dark:border-zinc-700/70">
            <div class="min-w-0 flex flex-col gap-2">

            @if ($resolvedTitle !== null)
                <h2 class="cp-modal-title text-base font-semibold leading-none text-zinc-900 dark:text-zinc-100">
                    {{ $resolvedTitle }}
                </h2>
            @else
                <div></div>
            @endif

            @if($description)
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
             @endif
            </div>


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

    <main class="cp-modal-body min-h-0 flex flex-1 flex-col overscroll-contain overflow-y-auto px-5 py-4">
        {{ $slot }}
    </main>

    @if ($hasFooter)
        <footer {{ $footer->attributes->class('cp-modal-footer flex shrink-0 items-center border-t border-zinc-200/70 px-5 py-2.5 dark:border-zinc-700/70') }}>
            {{ $footer }}
        </footer>
    @endif
</section>
