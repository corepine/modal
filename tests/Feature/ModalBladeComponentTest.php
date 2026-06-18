<?php

use Illuminate\Support\Facades\Blade;

it('renders modal host using corepine-modal component', function (): void {
    $html = Blade::render('<x-corepine-modal />');

    expect($html)->toContain('fixed inset-0 z-[999] overflow-y-auto');
    expect($html)->not->toContain('vendor/corepine-modal/app.css');
});

it('renders modal host using dotted assets alias', function (): void {
    $html = Blade::render('<x-corepine.modal.assets />');

    expect($html)->toContain('fixed inset-0 z-[999] overflow-y-auto');
    expect($html)->not->toContain('vendor/corepine-modal/app.css');
});

it('renders standalone dotted modal component without livewire host dependency', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal id="standalone-user-modal" heading="Standalone Modal" description="Simple Blade-only modal.">
    <div>Standalone body</div>
    <x-slot:footer>
        <button type="button">Done</button>
    </x-slot:footer>
</x-corepine.modal>
BLADE);

    expect($html)->toContain('data-corepine-modal-id="standalone-user-modal"');
    expect($html)->toContain('x-teleport="body"');
    expect($html)->toContain('x-on:resize.window.debounce.120ms="handleViewportResize()"');
    expect($html)->toContain("open: 'modal.open'");
    expect($html)->toContain("close: 'modal.close'");
    expect($html)->toContain("toggle: 'modal.toggle'");
    expect($html)->toContain('registerWindowListener(this.eventNames.open');
    expect($html)->toContain('Livewire.on(this.eventNames.open');
    expect($html)->toContain('h-[50dvh] max-h-[calc(100dvh-1rem)]');
    expect($html)->toContain('Standalone Modal');
    expect($html)->toContain('Simple Blade-only modal.');
    expect($html)->toContain('Standalone body');
    expect($html)->toContain('Done');
});

it('supports standalone custom header slot with merged header attributes', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal
    id="standalone-header-slot"
    heading="Default Heading"
    description="Default Description"
    show-close="false"
>
    <x-slot:header class="font-bold text-lg" data-testid="standalone-custom-header">
        <div>Custom Header Content</div>
    </x-slot:header>

    <div>Standalone body</div>
</x-corepine.modal>
BLADE);

    expect($html)->toContain('flex items-start justify-between gap-3 border-b');
    expect($html)->toContain('font-bold');
    expect($html)->toContain('data-testid="standalone-custom-header"');
    expect($html)->toContain('Custom Header Content');
    expect($html)->toContain('Standalone body');
    expect($html)->not->toContain('Default Heading');
    expect($html)->not->toContain('Default Description');
});

it('treats standalone empty header slot as explicit and hides built-in close action', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal
    id="standalone-empty-header-slot"
    heading="Default Heading"
    description="Default Description"
    show-close="true"
>
    <x-slot:header class="min-h-8"></x-slot:header>

    <div>Standalone body</div>
</x-corepine.modal>
BLADE);

    expect($html)->toContain('flex items-start justify-between gap-3 border-b');
    expect($html)->toContain('min-h-8');
    expect($html)->toContain('Standalone body');
    expect($html)->not->toContain('Default Heading');
    expect($html)->not->toContain('Default Description');
    expect($html)->not->toContain('x-on:click="close()"');
});

it('supports standalone modal presentation and sheet options', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal
    id="settings-sheet"
    type="bottomSheet"
    placement="bottom"
    origin="left"
    size="2xl"
    height="72vh"
    max-height="95vh"
    drag-close-threshold="0.35"
    dismissible="false"
    draggable="true"
    show-drag-handle="true"
    blur="true"
    class="border border-zinc-200"
>
    <div>Standalone settings</div>
</x-corepine.modal>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('rounded-t-2xl rounded-b-none');
    expect($flat)->toContain('origin-bottom');
    expect($flat)->toContain('max-w-2xl');
    expect($flat)->toContain('border border-zinc-200');
    expect($flat)->toContain('backdrop-blur-sm');
    expect($flat)->toContain('x-bind:style="panelStyle()"');
    expect($flat)->toContain('startSheetResize($event)');
    expect($flat)->toContain('classHeightHint(value)');
    expect($flat)->toContain('const preferredSource = this.heightValue ?? null;');
    expect($flat)->toContain('normalizedPreferredSource ?? classPreferred');
    expect($flat)->toContain('normalizePanelHeightValue(value, fallback = null)');
    expect($flat)->toContain('\u0022dragCloseThreshold\u0022:\u00220.35\u0022');
    expect($flat)->toContain('\u0022dismissible\u0022:false');
    expect($flat)->toContain('\u0022draggable\u0022:true');
    expect($flat)->toContain('\u0022maxHeight\u0022:\u002295vh\u0022');
    expect($flat)->toContain('\u0022showDragHandle\u0022:true');
});

it('renders standalone focus fallback and default sheet threshold', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal id="default-sheet" type="sheet">
    <div>Standalone sheet</div>
</x-corepine.modal>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('focusPanel()');
    expect($flat)->toContain('panel.contains(current)');
    expect($flat)->toContain('querySelector(\'[autofocus]\')');
    expect($flat)->toContain('panel.focus({ preventScroll: true })');
    expect($flat)->toContain('tabindex="-1"');
    expect($flat)->toContain('\u0022dragCloseThreshold\u0022:0.5');
    expect($flat)->toContain('dragCloseThresholdValue: options.dragCloseThreshold ?? 0.5');
    expect($flat)->toContain('return 0.5');
    expect($flat)->toContain('return this.preClosing && !this.snapClosing && this.isDrawer();');
    expect($flat)->toContain('if (!this.isDrawer()) { this.open = false;');
    expect($flat)->toContain('this.dispatchCloseEvents(payload); return;');
});

it('hides standalone close action by default when heading and description are empty', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal id="standalone-no-header-copy">
    <div>Standalone body</div>
</x-corepine.modal>
BLADE);

    expect($html)->toContain('Standalone body');
    expect($html)->not->toContain('x-on:click="close()"');
    expect($html)->not->toContain('sr-only');
});

it('allows standalone close action to be forced on without heading and description', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal id="standalone-force-close" show-close="true">
    <div>Standalone body</div>
</x-corepine.modal>
BLADE);

    expect($html)->toContain('Standalone body');
    expect($html)->toContain('x-on:click="close()"');
    expect($html)->toContain('sr-only');
});

it('renders standalone modal panel as a form when submit directives are present', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal id="standalone-form" heading="Edit User" wire:submit="save">
    <input type="text" name="name" />
</x-corepine.modal>
BLADE);

    expect($html)->toContain('<form');
    expect($html)->toContain('wire:submit="save"');
    expect($html)->toContain('method="post"');
    expect($html)->toContain('name="_token"');
    expect($html)->not->toContain('<section');
});

it('spoofs non-get methods on submit-aware standalone modal panels', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal id="standalone-form-patch" heading="Edit User" wire:submit="save" method="patch">
    <input type="text" name="name" />
</x-corepine.modal>
BLADE);

    expect($html)->toContain('<form');
    expect($html)->toContain('method="post"');
    expect($html)->toContain('name="_method"');
    expect($html)->toContain('value="PATCH"');
    expect($html)->toContain('name="_token"');
});

it('renders modal shell with header, body, and footer slots', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-layout heading="Edit User">
    <div>Modal body content</div>
    <x-slot:footer>
        <button type="button">Save</button>
    </x-slot:footer>
</x-corepine-modal-layout>
BLADE);

    expect($html)->toContain('overflow-hidden overscroll-contain');
    expect($html)->toContain('flex shrink-0 flex-col gap-0.5 border-b');
    expect($html)->toContain('flex min-h-7 items-center gap-3');
    expect($html)->toContain('min-h-0 flex flex-1 flex-col overflow-y-auto overscroll-contain px-5 py-4');
    expect($html)->toContain('flex shrink-0 items-center justify-end border-t');
    expect($html)->toContain('justify-end');
    expect($html)->toContain('min-h-0 flex flex-1');
    expect($html)->toContain('Edit User');
    expect($html)->toContain('Modal body content');
    expect($html)->toContain('Save');
});

it('renders modal description when provided', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-layout heading="Edit User" description="Review account details before saving.">
    <div>Modal body content</div>
</x-corepine-modal-layout>
BLADE);

    expect($html)->toContain('Edit User');
    expect($html)->toContain('Review account details before saving.');
    expect($html)->toContain('text-zinc-500');
});

it('hides layout close action by default when heading and description are empty', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-layout :heading="null">
    <div>Body only</div>
</x-corepine-modal-layout>
BLADE);

    expect($html)->toContain('Body only');
    expect($html)->not->toContain('sr-only');
    expect($html)->not->toContain('text-base font-semibold leading-none text-zinc-900 dark:text-zinc-100');
    expect($html)->toContain('h-full');
    expect($html)->not->toContain('flex shrink-0 items-center justify-end border-t');
});

it('allows layout close action to be forced on without heading and description', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-layout :heading="null" show-close="true">
    <div>Body only</div>
</x-corepine-modal-layout>
BLADE);

    expect($html)->toContain('Body only');
    expect($html)->toContain('sr-only');
    expect($html)->toContain('ml-auto inline-flex h-7 w-7');
});

it('renders layout child chrome as a back affordance', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-layout heading="Child Modal" description="Nested details stay on their own line." :child="true">
    <div>Body only</div>
</x-corepine-modal-layout>
BLADE);

    expect($html)->toContain('Child Modal');
    expect($html)->toContain('Nested details stay on their own line.');
    expect($html)->toContain('Back');
    expect($html)->toContain('flex min-h-7 items-center gap-3');
    expect($html)->toContain('h-7 w-5 shrink-0');
    expect($html)->toContain('p-0');
    expect($html)->toContain('size-5.5');
    expect($html)->toContain('M13 4L7 10L13 16');
    expect($html)->not->toContain('M5 5L15 15M15 5L5 15');
    expect(strpos($html, 'Back'))->toBeLessThan(strpos($html, 'Child Modal'));
    expect(strpos($html, 'Child Modal'))->toBeLessThan(strpos($html, 'Nested details stay on their own line.'));
});

it('can disable layout child back chrome', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-layout heading="Child Modal" :child="true" :stacked-back-button="false">
    <div>Body only</div>
</x-corepine-modal-layout>
BLADE);

    expect($html)->toContain('Child Modal');
    expect($html)->toContain('Close');
    expect($html)->toContain('M5 5L15 15M15 5L5 15');
    expect($html)->not->toContain('Back');
    expect($html)->not->toContain('M13 4L7 10L13 16');
});

it('documents Tailwind config sources for modal size classes', function (): void {
    $css = file_get_contents(__DIR__.'/../../resources/css/app.css');
    $readme = file_get_contents(__DIR__.'/../../README.md');

    expect($css)->toContain('@source "../../config/**/*.php";');
    expect($css)->toContain('@keyframes corepine-modal-panel-pre-close');
    expect($readme)->toContain('@source "../../config/corepine-modal.php";');
    expect($readme)->toContain('| `dragCloseThreshold` | `float` | `0.5` |');
    expect($readme)->toContain('| `stackedBackButton` | `bool` | `true` |');
});

it('merges custom wrapper attributes on modal shell', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-layout heading="Custom" id="users-modal" class="ring-1 ring-zinc-300">
    <div>Body</div>
</x-corepine-modal-layout>
BLADE);

    expect($html)->toContain('id="users-modal"');
    expect($html)->toContain('ring-1');
    expect($html)->toContain('overflow-hidden overscroll-contain');
});

it('renders modal shell using template alias', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-template heading="Template Alias">
    <div>Template content</div>
</x-corepine-modal-template>
BLADE);

    expect($html)->toContain('overflow-hidden overscroll-contain');
    expect($html)->toContain('Template Alias');
    expect($html)->toContain('Template content');
});

it('renders modal shell using dotted layout alias', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal.layout heading="Dot Layout">
    <div>Layout content</div>
</x-corepine.modal.layout>
BLADE);

    expect($html)->toContain('overflow-hidden overscroll-contain');
    expect($html)->toContain('Dot Layout');
    expect($html)->toContain('Layout content');
});

it('lets layout header slot override heading, description, and close action', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal.layout
    heading="Manage Users"
    description="Search and view users in your system."
    show-close="true"
>
    <x-slot:header class="font-bold text-lg" data-testid="layout-custom-header">
        <h2>Our Modals</h2>
    </x-slot:header>

    <div>Layout content</div>
</x-corepine.modal.layout>
BLADE);

    expect($html)->toContain('data-testid="layout-custom-header"');
    expect($html)->toContain('font-bold');
    expect($html)->toContain('Our Modals');
    expect($html)->toContain('Layout content');
    expect($html)->not->toContain('Manage Users');
    expect($html)->not->toContain('Search and view users in your system.');
    expect($html)->not->toContain('sr-only');
});

it('treats empty layout header slot as explicit and hides built-in chrome', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal.layout
    heading="Manage Users"
    description="Search and view users in your system."
    show-close="true"
>
    <x-slot:header class="min-h-8"></x-slot:header>

    <div>Layout content</div>
</x-corepine.modal.layout>
BLADE);

    expect($html)->toContain('min-h-8');
    expect($html)->toContain('Layout content');
    expect($html)->not->toContain('Manage Users');
    expect($html)->not->toContain('Search and view users in your system.');
    expect($html)->not->toContain('sr-only');
});

it('supports inline footer component inside modal layout body', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-layout heading="Inline Footer">
    <div>Body content</div>

    <x-corepine.modal.footer id="users-footer-actions" class="w-full">
        <div class="inline-footer-actions flex justify-end gap-2">
            <button type="button">Inline Save</button>
        </div>
    </x-corepine.modal.footer>
</x-corepine-modal-layout>
BLADE);

    expect($html)->toContain('flex shrink-0 items-center justify-end border-t');
    expect($html)->toContain('id="users-footer-actions"');
    expect($html)->toContain('inline-footer-actions');
    expect($html)->toContain('Inline Save');
    expect($html)->toContain('Body content');
    expect($html)->not->toContain('<corepine-modal-footer');
    expect($html)->not->toContain('corepine-modal-footer:start');
});

it('renders layout root as a form when submit directives are present', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-layout heading="Edit User" wire:submit="save">
    <input type="text" name="name" />
</x-corepine-modal-layout>
BLADE);

    expect($html)->toContain('<form');
    expect($html)->toContain('wire:submit="save"');
    expect($html)->toContain('method="post"');
    expect($html)->toContain('name="_token"');
    expect($html)->not->toContain('<section');
});

it('spoofs non-get form methods on submit-aware layouts', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-layout heading="Edit User" wire:submit="save" method="patch">
    <input type="text" name="name" />
</x-corepine-modal-layout>
BLADE);

    expect($html)->toContain('<form');
    expect($html)->toContain('method="post"');
    expect($html)->toContain('name="_method"');
    expect($html)->toContain('value="PATCH"');
    expect($html)->toContain('name="_token"');
});

it('renders layout root as a form for alpine submit handlers too', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal-layout heading="Edit User" x-on:submit.prevent="save()">
    <input type="text" name="name" />
</x-corepine-modal-layout>
BLADE);

    expect($html)->toContain('<form');
    expect($html)->toContain('x-on:submit.prevent="save()"');
    expect($html)->toContain('name="_token"');
    expect($html)->not->toContain('<section');
});

it('renders open helper with new alias and forwards class to modal attributes', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal.actions.open
    component="modals.edit-user"
    :arguments="['user' => 5]"
    class="p-8 rounded-2xl"
    size="2xl"
    height="70vh"
    blur="true"
    drawer="true"
    isolate="true"
    placement="left"
    origin="left"
    data-testid="open-trigger"
>
    <button type="button">Edit</button>
</x-corepine.modal.actions.open>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('data-testid="open-trigger"');
    expect($flat)->toContain('Livewire.dispatch');
    expect($flat)->toContain('modal.open');
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
    expect($flat)->toContain('\u0022placement\u0022:\u0022left\u0022');
    expect($flat)->toContain('\u0022origin\u0022:\u0022left\u0022');
    expect($flat)->not->toContain('class="p-8 rounded-2xl"');
});

it('supports explicit bottom sheet aliases and presentation props on open helper', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal.actions.open
    component="modals.edit-user"
    type="bottomSheet"
    dismissible="false"
    draggable="true"
    show-drag-handle="true"
    close-all-on-escape="true"
>
    <button type="button">Edit</button>
</x-corepine.modal.actions.open>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('\u0022type\u0022:\u0022sheet\u0022');
    expect($flat)->toContain('\u0022dismissible\u0022:false');
    expect($flat)->toContain('\u0022draggable\u0022:true');
    expect($flat)->toContain('\u0022showDragHandle\u0022:true');
    expect($flat)->toContain('\u0022closeAllOnEscape\u0022:true');
});

it('supports shell chrome props on open helper', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal.actions.open
    component="modals.edit-user"
    shell="true"
    heading="Manage Users"
    description="Search and view users in your system."
    show-close="true"
    stacked-back-button="false"
    footer-actions-alignment="center"
    :actions="[
        ['type' => 'close', 'label' => 'Cancel'],
        ['type' => 'method', 'method' => 'saveUsers', 'label' => 'Save'],
    ]"
>
    <button type="button">Edit</button>
</x-corepine.modal.actions.open>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('\u0022shell\u0022:true');
    expect($flat)->toContain('\u0022heading\u0022:\u0022Manage Users\u0022');
    expect($flat)->toContain('\u0022description\u0022:\u0022Search and view users in your system.\u0022');
    expect($flat)->toContain('\u0022showClose\u0022:true');
    expect($flat)->toContain('\u0022stackedBackButton\u0022:false');
    expect($flat)->toContain('\u0022footerActionsAlignment\u0022:\u0022center\u0022');
    expect($flat)->toContain('\u0022actions\u0022');
    expect($flat)->toContain('saveUsers');
});

it('supports post-close dispatch payloads on open helper', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal.actions.open
    component="modals.edit-user"
    :dispatch="['users-refreshed' => ['user' => 5]]"
    :dispatch-to="['orders.table' => ['sync-user' => ['user' => 5]]]"
>
    <button type="button">Edit</button>
</x-corepine.modal.actions.open>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('\u0022dispatch\u0022');
    expect($flat)->toContain('users-refreshed');
    expect($flat)->toContain('\u0022dispatchTo\u0022');
    expect($flat)->toContain('orders.table');
    expect($flat)->toContain('sync-user');
});

it('emits isolate attribute on open helper', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal.actions.open
    component="modals.edit-user"
    isolate="true"
>
    <button type="button">Edit</button>
</x-corepine.modal.actions.open>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('\u0022isolate\u0022:true');
});

it('supports Livewire component classes through the component prop on open helper', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal.actions.open :component="\Corepine\Modal\Tests\Fixtures\Livewire\ExampleModal::class">
    <button type="button">Open</button>
</x-corepine.modal.actions.open>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('ExampleModal');
    expect($flat)->toContain('modal.open');
});

it('supports targeted standalone open helper payloads by modal id', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal.actions.open modal-id="user-sheet">
    <button type="button">Open</button>
</x-corepine.modal.actions.open>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('window.dispatchEvent(new CustomEvent');
    expect($flat)->toContain('modal.open');
    expect($flat)->toContain("const standalonePayload = { id: 'user-sheet' }");
    expect($flat)->toContain('Livewire.dispatch');
});

it('merges modalAttributes class with incoming class on open helper', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal.actions.open
    component="modals.edit-user"
    :modal-attributes="['class' => 'bg-white border border-zinc-200']"
    class="rounded-3xl shadow-xl"
>
    <button type="button">Edit</button>
</x-corepine.modal.actions.open>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('bg-white border border-zinc-200 rounded-3xl shadow-xl');
});

it('renders close helper with new alias and default close payload', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal.actions.close data-testid="close-trigger">
    <button type="button">Close</button>
</x-corepine.modal.actions.close>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('data-testid="close-trigger"');
    expect($flat)->toContain('Livewire.dispatch');
    expect($flat)->toContain('modal.close');
    expect($flat)->toMatch('/layers:\s*1/');
    expect($flat)->toMatch('/destroy:\s*true/');
});

it('renders close helper close-all path and dispatches close-all event', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal.actions.close :close-all="true" :destroy="false">
    <button type="button">Close All</button>
</x-corepine.modal.actions.close>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toMatch('/if\s*\(\s*true\s*\)/');
    expect($flat)->toContain('Livewire.dispatch');
    expect($flat)->toContain('modal.close-all');
    expect($flat)->toMatch('/destroy:\s*false/');
});

it('renders close helper with post-close dispatch payloads', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal.actions.close
    :dispatch="['users-refreshed' => ['user' => 5]]"
    :dispatch-to="['orders.table' => ['sync-user' => ['user' => 5]]]"
>
    <button type="button">Close</button>
</x-corepine.modal.actions.close>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('users-refreshed');
    expect($flat)->toContain('orders.table');
    expect($flat)->toContain('sync-user');
});

it('supports targeted standalone close helper payloads by modal id', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine.modal.actions.close modal-id="user-sheet">
    <button type="button">Close</button>
</x-corepine.modal.actions.close>
BLADE);

    $flat = preg_replace('/\s+/', ' ', html_entity_decode($html, ENT_QUOTES));

    expect($flat)->toContain('data-corepine-modal-id');
    expect($flat)->toContain('user-sheet');
    expect($flat)->toContain('window.dispatchEvent(new CustomEvent');
    expect($flat)->toContain('modal.close');
    expect($flat)->toContain('id: resolvedModalId');
});

it('keeps backward-compatible open and close aliases', function (): void {
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
    expect($openDecoded)->toContain('modal.open');
    expect($closeDecoded)->toContain('Livewire.dispatch');
    expect($closeDecoded)->toContain('modal.close');
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
    expect($openDecoded)->toContain('modal.open');
    expect($closeDecoded)->toContain('Livewire.dispatch');
    expect($closeDecoded)->toContain('modal.close');
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
    expect($openDecoded)->toContain('modal.open');
    expect($closeDecoded)->toContain('Livewire.dispatch');
    expect($closeDecoded)->toContain('modal.close');
});
