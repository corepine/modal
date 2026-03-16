<?php

use Illuminate\Support\Facades\Blade;

it('renders modal host using corepine-modal component', function (): void {
    $html = Blade::render('<x-corepine-modal />');

    expect($html)->toContain('cp-modal fixed inset-0');
});

it('renders modal shell with header, body, and footer slots', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-layout title="Edit User">
    <div>Modal body content</div>
    <x-slot:footer>
        <button type="button">Save</button>
    </x-slot:footer>
</x-corepine-modal-layout>
BLADE);

    expect($html)->toContain('cp-modal-layout');
    expect($html)->toContain('cp-modal-header');
    expect($html)->toContain('cp-modal-body');
    expect($html)->toContain('cp-modal-footer');
    expect($html)->toContain('h-[calc(100vh_-_7.5rem)]');
    expect($html)->toContain('Edit User');
    expect($html)->toContain('Modal body content');
    expect($html)->toContain('Save');
});

it('renders modal description when provided', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-layout title="Edit User" description="Review account details before saving.">
    <div>Modal body content</div>
</x-corepine-modal-layout>
BLADE);

    expect($html)->toContain('Edit User');
    expect($html)->toContain('Review account details before saving.');
    expect($html)->toContain('text-zinc-500');
});

it('supports null title while keeping close action by default', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-layout :title="null">
    <div>Body only</div>
</x-corepine-modal-layout>
BLADE);

    expect($html)->toContain('Body only');
    expect($html)->toContain('sr-only');
    expect($html)->not->toContain('cp-modal-title');
    expect($html)->toContain('h-full');
    expect($html)->not->toContain('cp-modal-footer');
});

it('merges custom wrapper attributes on modal shell', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-layout title="Custom" id="users-modal" class="ring-1 ring-zinc-300">
    <div>Body</div>
</x-corepine-modal-layout>
BLADE);

    expect($html)->toContain('id="users-modal"');
    expect($html)->toContain('ring-1');
    expect($html)->toContain('cp-modal-layout');
});

it('renders modal shell using template alias', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-template title="Template Alias">
    <div>Template content</div>
</x-corepine-modal-template>
BLADE);

    expect($html)->toContain('cp-modal-layout');
    expect($html)->toContain('Template Alias');
    expect($html)->toContain('Template content');
});

it('renders open helper with new alias and forwards class to modal attributes', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-open
    component="modals.edit-user"
    :arguments="['user' => 5]"
    class="p-8 rounded-2xl"
    size="2xl"
    blur="true"
    drawer="true"
    isolate="true"
    position="left"
    data-testid="open-trigger"
>
    <button type="button">Edit</button>
</x-corepine-modal-open>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('data-testid="open-trigger"');
    expect($flat)->toContain('Livewire.dispatch');
    expect($flat)->toContain('openModal');
    expect($flat)->toContain('modals.edit-user');
    expect($flat)->toContain('arguments: JSON.parse');
    expect($flat)->toContain('\u0022user\u0022:5');
    expect($flat)->toContain('modalAttributes: JSON.parse');
    expect($flat)->toContain('p-8 rounded-2xl');
    expect($flat)->toContain('\u0022size\u0022:\u00222xl\u0022');
    expect($flat)->toContain('\u0022blur\u0022:true');
    expect($flat)->toContain('\u0022drawer\u0022:true');
    expect($flat)->toContain('\u0022isolate\u0022:true');
    expect($flat)->toContain('\u0022position\u0022:\u0022left\u0022');
    expect($flat)->not->toContain('class="p-8 rounded-2xl"');
});

it('supports legacy isolated prop but emits isolate attribute', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-open
    component="modals.edit-user"
    isolated="true"
>
    <button type="button">Edit</button>
</x-corepine-modal-open>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('\u0022isolate\u0022:true');
    expect($flat)->not->toContain('\u0022isolated\u0022:true');
});

it('merges modalAttributes class with incoming class on open helper', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-open
    component="modals.edit-user"
    :modal-attributes="['class' => 'bg-white border border-zinc-200']"
    class="rounded-3xl shadow-xl"
>
    <button type="button">Edit</button>
</x-corepine-modal-open>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('bg-white border border-zinc-200 rounded-3xl shadow-xl');
});

it('renders close helper with new alias and default close payload', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-close data-testid="close-trigger">
    <button type="button">Close</button>
</x-corepine-modal-close>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('data-testid="close-trigger"');
    expect($flat)->toContain('Livewire.dispatch');
    expect($flat)->toContain('closeModal');
    expect($flat)->toMatch('/count:\s*1/');
    expect($flat)->toMatch('/destroy:\s*true/');
});

it('renders close helper force path and dispatches close-all event', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-close :force="true" :destroy="false">
    <button type="button">Close All</button>
</x-corepine-modal-close>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toMatch('/if\s*\(\s*true\s*\)/');
    expect($flat)->toContain('Livewire.dispatch');
    expect($flat)->toContain('closeAllModals');
    expect($flat)->toMatch('/destroy:\s*false/');
});

it('keeps backward-compatible open and close aliases', function (): void {
    $open = Blade::render(<<<'BLADE'
<x-corepine-open-modal component="modals.edit-user">
    <button type="button">Open</button>
</x-corepine-open-modal>
BLADE);
    $close = Blade::render(<<<'BLADE'
<x-corepine-close-modal>
    <button type="button">Close</button>
</x-corepine-close-modal>
BLADE);

    $openDecoded = preg_replace('/\s+/', ' ', html_entity_decode($open, ENT_QUOTES));
    $closeDecoded = preg_replace('/\s+/', ' ', html_entity_decode($close, ENT_QUOTES));

    expect($openDecoded)->toContain('Livewire.dispatch');
    expect($openDecoded)->toContain('openModal');
    expect($closeDecoded)->toContain('Livewire.dispatch');
    expect($closeDecoded)->toContain('closeModal');
});
