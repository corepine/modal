@props([
    'includeStyles' => false,
    'host' => false,
    'title' => null,
    'showClose' => true,
])

@php($hasDefaultSlot = trim((string) $slot) !== '')
@php($renderHost = (bool) $host || (! $hasDefaultSlot && ! isset($footer)))

@if ($renderHost)
    @if ($includeStyles)
        <link rel="stylesheet" href="{{ asset('vendor/corepine-modal/app.css') }}">
    @endif

    @php($modalConfig = app(\Corepine\Modal\Support\ModalConfig::class))
    @livewire($modalConfig->hostComponent())
@else
    @php($resolvedTitle = is_string($title) && trim($title) !== '' ? $title : null)

    <section {{ $attributes->merge(['class'=>'cp-modal-layout relative flex min-h-full h-full bg-inherit flex-col']) }}>
        @if ($resolvedTitle !== null || $showClose)
            <header class="cp-modal-header flex items-center sticky justify-between gap-3 border-b border-zinc-200/70 px-5 py-4 dark:border-zinc-700/70">
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

        <main
        @class([
            'cp-modal-body overflow-y-auto px-5 py-4',
            'h-full'=>$footer->isEmpty(),
            'h-[85%]'=>!$footer->isEmpty(),
        ])>
            {{ $slot }}
        </main>

        @isset($footer)
            <footer  {{ $footer->attributes->class(['cp-modal-footer mt-auto  border border-zinc-200/70 px-5 py-2 dark:border-zinc-700/70']) }} >
                {{ $footer }}
            </footer>
        @endisset
    </section>
@endif
