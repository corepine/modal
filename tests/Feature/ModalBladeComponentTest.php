<?php

use Illuminate\Support\Facades\Blade;

it('renders modal host using corepine-modal component', function (): void {
    $html = Blade::render('<x-corepine-modal />');

    expect($html)->toContain('cp-modal fixed inset-0');
});

it('renders modal host using dotted assets alias', function (): void {
    $html = Blade::render('<x-corepine.modal.assets />');

    expect($html)->toContain('cp-modal fixed inset-0');
});

it('renders standalone dotted modal component without livewire host dependency', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal id="standalone-user-modal" title="Standalone Modal" description="Simple Blade-only modal.">
    <div>Standalone body</div>
    <x-slot:footer>
        <button type="button">Done</button>
    </x-slot:footer>
</x-corepine.modal>
BLADE);

    expect($html)->toContain('data-corepine-modal-id="standalone-user-modal"');
    expect($html)->toContain('x-teleport="body"');
    expect($html)->toContain('x-on:resize.window.debounce.120ms="handleViewportResize()"');
    expect($html)->toContain('x-on:corepine-modal:open.window');
    expect($html)->toContain('x-on:corepine-modal:close.window');
    expect($html)->toContain('x-on:corepine-modal:toggle.window');
    expect($html)->toContain('cp-modal-panel-default-height');
    expect($html)->toContain('Standalone Modal');
    expect($html)->toContain('Simple Blade-only modal.');
    expect($html)->toContain('Standalone body');
    expect($html)->toContain('Done');
});

it('supports standalone modal presentation and sheet options', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal
    id="settings-sheet"
    type="sheet"
    position="bottom"
    size="2xl"
    height="72vh"
    sheet-min-height="40vh"
    sheet-max-height="95vh"
    drag-close-threshold="0.35"
    close-on-click-away="false"
    blur="true"
    class="border border-zinc-200"
>
    <div>Standalone settings</div>
</x-corepine.modal>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('cp-modal-shape-sheet');
    expect($flat)->toContain('max-w-2xl');
    expect($flat)->toContain('border border-zinc-200');
    expect($flat)->toContain('backdrop-blur-sm');
    expect($flat)->toContain('x-bind:style="panelStyle()"');
    expect($flat)->toContain('startSheetResize($event)');
    expect($flat)->toContain('\u0022dragCloseThreshold\u0022:\u00220.35\u0022');
    expect($flat)->toContain('\u0022closeOnClickAway\u0022:false');
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
    expect($html)->toContain('min-h-0 flex flex-1');
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

it('renders modal shell using dotted layout alias', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal.layout title="Dot Layout">
    <div>Layout content</div>
</x-corepine.modal.layout>
BLADE);

    expect($html)->toContain('cp-modal-layout');
    expect($html)->toContain('Dot Layout');
    expect($html)->toContain('Layout content');
});

it('renders open helper with new alias and forwards class to modal attributes', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-open
    component="modals.edit-user"
    :arguments="['user' => 5]"
    class="p-8 rounded-2xl"
    size="2xl"
    height="70vh"
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
    expect($flat)->toContain('\u0022height\u0022:\u002270vh\u0022');
    expect($flat)->toContain('\u0022blur\u0022:true');
    expect($flat)->toContain('\u0022drawer\u0022:true');
    expect($flat)->toContain('\u0022type\u0022:\u0022drawer\u0022');
    expect($flat)->toContain('\u0022isolate\u0022:true');
    expect($flat)->toContain('\u0022position\u0022:\u0022left\u0022');
    expect($flat)->not->toContain('class="p-8 rounded-2xl"');
});

it('supports explicit type prop on open helper', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-open
    component="modals.edit-user"
    type="sheet"
>
    <button type="button">Edit</button>
</x-corepine-modal-open>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('\u0022type\u0022:\u0022sheet\u0022');
});

it('supports layout chrome props on open helper', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-open
    component="modals.edit-user"
    layout="true"
    plain="false"
    title="Manage Users"
    description="Search and view users in your system."
    show-close="true"
    :footer-actions="[
        ['type' => 'close', 'label' => 'Cancel'],
        ['type' => 'method', 'method' => 'saveUsers', 'label' => 'Save'],
    ]"
>
    <button type="button">Edit</button>
</x-corepine-modal-open>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('\u0022layout\u0022:true');
    expect($flat)->toContain('\u0022plain\u0022:false');
    expect($flat)->toContain('\u0022title\u0022:\u0022Manage Users\u0022');
    expect($flat)->toContain('\u0022description\u0022:\u0022Search and view users in your system.\u0022');
    expect($flat)->toContain('\u0022showClose\u0022:true');
    expect($flat)->toContain('\u0022footerActions\u0022');
    expect($flat)->toContain('saveUsers');
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

it('supports dotted actions open and close helper aliases', function (): void {
    $open = Blade::render(<<<'BLADE'
<x-corepine.modal.actions.open component="modals.edit-user">
    <button type="button">Open</button>
</x-corepine.modal.actions.open>
BLADE);
    $close = Blade::render(<<<'BLADE'
<x-corepine.modal.actions.close>
    <button type="button">Close</button>
</x-corepine.modal.actions.close>
BLADE);

    $openDecoded = preg_replace('/\s+/', ' ', html_entity_decode($open, ENT_QUOTES));
    $closeDecoded = preg_replace('/\s+/', ' ', html_entity_decode($close, ENT_QUOTES));

    expect($openDecoded)->toContain('Livewire.dispatch');
    expect($openDecoded)->toContain('openModal');
    expect($closeDecoded)->toContain('Livewire.dispatch');
    expect($closeDecoded)->toContain('closeModal');
});

it('supports dashed actions open and close aliases', function (): void {
    $open = Blade::render(<<<'BLADE'
<x-corepine-modal-actions-open component="modals.edit-user">
    <button type="button">Open</button>
</x-corepine-modal-actions-open>
BLADE);
    $close = Blade::render(<<<'BLADE'
<x-corepine-modal-actions-close>
    <button type="button">Close</button>
</x-corepine-modal-actions-close>
BLADE);

    $openDecoded = preg_replace('/\s+/', ' ', html_entity_decode($open, ENT_QUOTES));
    $closeDecoded = preg_replace('/\s+/', ' ', html_entity_decode($close, ENT_QUOTES));

    expect($openDecoded)->toContain('Livewire.dispatch');
    expect($openDecoded)->toContain('openModal');
    expect($closeDecoded)->toContain('Livewire.dispatch');
    expect($closeDecoded)->toContain('closeModal');
});
