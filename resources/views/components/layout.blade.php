@props([
    'heading' => null,
    'description' => null,
    'showClose' => null,
    'modalType' => null,
])

@php
    $resolvedHeading = is_string($heading) && trim($heading) !== '' ? $heading : null;
    $resolvedDescription = is_string($description) && trim($description) !== '' ? $description : null;
    $hasHeaderSlot = isset($header);
    $namedHeader = $hasHeaderSlot ? $header : null;
    $namedFooter = isset($footer) && $footer->isNotEmpty() ? $footer : null;
    $renderedBody = $slot;
    $inlineFooterBlocks = [];
    $normalizeBoolean = static function (mixed $value, ?bool $fallback = null): ?bool {
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
    };
    $resolvedShowClose = ! is_null($showClose)
        ? $normalizeBoolean($showClose, true)
        : ($resolvedHeading !== null || $resolvedDescription !== null);
    $resolvedModalType = $modalType instanceof \Corepine\Modal\Enums\ModalType
        ? $modalType->value
        : (is_string($modalType) ? strtolower(trim($modalType)) : null);
    $isSheetLayout = in_array($resolvedModalType, ['sheet', 'bottomsheet', 'bottom-sheet', 'bottom_sheet'], true);
    $footerClasses = $isSheetLayout
        ? 'flex shrink-0 items-center justify-end border-zinc-200/70 px-5 py-1.5 sm:py-2 dark:border-zinc-700/70'
        : 'flex shrink-0 items-center justify-end border-zinc-200/70 px-5 py-2.5 dark:border-zinc-700/70';
    $attributeMap = method_exists($attributes, 'getAttributes') ? $attributes->getAttributes() : iterator_to_array($attributes);
    $hasSubmitAttribute = collect(array_keys($attributeMap))->contains(
        static fn (string $key): bool => str_starts_with($key, 'wire:submit')
            || str_starts_with($key, 'x-on:submit')
            || str_starts_with($key, '@submit')
    );
    $isFormLayout = $hasSubmitAttribute;
    $rootAttributes = $isFormLayout ? $attributes->except('method') : $attributes;
    $submittedMethod = is_string($attributes->get('method')) ? strtolower(trim((string) $attributes->get('method'))) : null;
    $formMethod = $submittedMethod;

    if ($isFormLayout) {
        if (! in_array($formMethod, ['get', 'post', 'put', 'patch', 'delete'], true)) {
            $formMethod = 'post';
        }

        $rootAttributes = $rootAttributes->merge([
            'method' => in_array($formMethod, ['put', 'patch', 'delete'], true) ? 'post' : $formMethod,
        ]);
    }

    if ($namedFooter === null) {
        $slotHtml = (string) $slot;

        if (str_contains($slotHtml, 'corepine-modal-footer:start')) {
            $inlineFooterPattern = '/<!--\s*corepine-modal-footer:start\s*-->(.*?)<!--\s*corepine-modal-footer:end\s*-->/is';
            preg_match_all($inlineFooterPattern, $slotHtml, $inlineFooterMatches);
            $rawInlineFooterBlocks = $inlineFooterMatches[1] ?? [];

            if ($rawInlineFooterBlocks !== []) {
                $inlineFooterBlocks = array_values(array_filter(
                    array_map(static fn (string $block): string => trim($block), $rawInlineFooterBlocks),
                    static fn (string $block): bool => $block !== '',
                ));

                $renderedBody = new \Illuminate\Support\HtmlString(
                    trim((string) preg_replace($inlineFooterPattern, '', $slotHtml))
                );
            }
        }
    }

    $hasInlineFooter = $inlineFooterBlocks !== [];
    $hasFooter = $namedFooter !== null || $hasInlineFooter;
@endphp

@if ($isFormLayout)
    <form {{ $rootAttributes->merge(['class' => 'h-full max-h-full min-h-0 flex flex-col overflow-hidden overscroll-contain bg-inherit dark:bg-zinc-800 dark:text-white']) }}>
        @if ($formMethod !== 'get')
            {!! csrf_field() !!}
        @endif

        @if (in_array($formMethod, ['put', 'patch', 'delete'], true))
            {!! method_field(strtoupper($formMethod)) !!}
        @endif
@else
    <section {{ $rootAttributes->merge(['class' => 'h-full max-h-full min-h-0 flex flex-col overflow-hidden overscroll-contain bg-inherit dark:bg-zinc-800 dark:text-white']) }}>
@endif
    @if ($namedHeader !== null || $resolvedHeading !== null || $resolvedDescription !== null || $resolvedShowClose)
        <header
            @if ($namedHeader !== null)
                {{ $namedHeader->attributes->class('flex shrink-0 items-start justify-between gap-3 border-b border-zinc-200/70 px-5 py-4 dark:border-zinc-700/70') }}
            @else
                class="flex shrink-0 items-start justify-between gap-3 border-b border-zinc-200/70 px-5 py-4 dark:border-zinc-700/70"
            @endif
        >
            @if ($namedHeader !== null)
                {{ $namedHeader }}
            @else
                <div class="min-w-0 flex flex-col gap-2">
                    @if ($resolvedHeading !== null)
                        <h2 class="text-base font-semibold leading-none text-zinc-900 dark:text-zinc-100">
                            {{ $resolvedHeading }}
                        </h2>
                    @endif

                    @if ($resolvedDescription !== null)
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $resolvedDescription }}</p>
                    @endif
                </div>
            @endif

            @if ($resolvedShowClose && $namedHeader === null)
                <x-corepine.modal.actions.close
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                >
                    <span class="sr-only">Close</span>
                    <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" aria-hidden="true">
                        <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
                    </svg>
                </x-corepine.modal.actions.close>
            @endif
        </header>
    @endif

    <main class="min-h-0 flex flex-1 flex-col overflow-y-auto overscroll-contain px-5 py-4">
        @if ($hasInlineFooter)
            {!! $renderedBody !!}
        @else
            {{ $renderedBody }}
        @endif
    </main>

    @if ($namedFooter !== null)
        <footer {{ $namedFooter->attributes->class($footerClasses) }}>
            {{ $namedFooter }}
        </footer>
    @elseif ($hasInlineFooter)
        <footer class="{{ $footerClasses }}">
            @foreach ($inlineFooterBlocks as $inlineFooterBlock)
                {!! $inlineFooterBlock !!}
            @endforeach
        </footer>
    @endif
@if ($isFormLayout)
    </form>
@else
    </section>
@endif
