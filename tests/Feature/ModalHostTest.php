<?php

use Corepine\Modal\Livewire\ModalHost;
use Corepine\Modal\Tests\Fixtures\Livewire\AttributeSizedModal;
use Corepine\Modal\Tests\Fixtures\Livewire\ExampleModal;
use Livewire\Livewire;

beforeEach(function (): void {
    Livewire::component('test.example-modal', ExampleModal::class);
    Livewire::component('test.attribute-sized-modal', AttributeSizedModal::class);
});

it('opens and stacks modals', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal')
        ->dispatch('openModal', component: 'test.example-modal', arguments: ['title' => 'Second']);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($stack)->toHaveCount(2);
    expect($test->get('activeModalId'))->toBe($stack[1]);
    expect($modals[$stack[1]]['arguments']['title'])->toBe('Second');
});

it('closes top modal layers', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', arguments: ['title' => 'One'])
        ->dispatch('openModal', component: 'test.example-modal', arguments: ['title' => 'Two'])
        ->dispatch('openModal', component: 'test.example-modal', arguments: ['title' => 'Three']);

    $initialStack = $test->get('stack');

    $test->dispatch('closeTopModal', count: 2, destroy: true);

    $stack = $test->get('stack');

    expect($stack)->toHaveCount(1);
    expect($test->get('activeModalId'))->toBe($stack[0]);
    expect($stack[0])->toBe($initialStack[0]);
});

it('force closes all modals', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal')
        ->dispatch('openModal', component: 'test.example-modal')
        ->dispatch('closeTopModal', force: true);

    expect($test->get('stack'))->toBe([]);
    expect($test->get('modals'))->toBe([]);
    expect($test->get('activeModalId'))->toBeNull();
});

it('opens modal by class path', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: ExampleModal::class);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($stack)->toHaveCount(1);
    expect($modals[$stack[0]]['class'])->toBe(ExampleModal::class);
});

it('uses modalSize from component class', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal');

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['size'])->toBe('md');
});

it('allows runtime size override with raw classes', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', modalAttributes: [
            'size' => 'max-w-[900px] sm:max-w-full',
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['size'])->toBe('max-w-[900px] sm:max-w-full');
});

it('stores runtime class and blur attributes', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', modalAttributes: [
            'class' => 'p-8 border border-zinc-200',
            'blur' => true,
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['class'])->toBe('p-8 border border-zinc-200');
    expect($modals[$stack[0]]['modalAttributes']['blur'])->toBeTrue();
});

it('stores drawer and position attributes', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', modalAttributes: [
            'drawer' => true,
            'position' => 'left',
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['drawer'])->toBeTrue();
    expect($modals[$stack[0]]['modalAttributes']['position'])->toBe('left');
    $test->assertSee('cp-modal-panel-drawer-height');
});

it('stores explicit sheet type and renders sheet classes', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', modalAttributes: [
            'type' => 'sheet',
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['type'])->toBe('sheet');
    expect($modals[$stack[0]]['modalAttributes']['sheet'])->toBeTrue();
    expect($modals[$stack[0]]['modalAttributes']['drawer'])->toBeFalse();
    expect($modals[$stack[0]]['modalAttributes']['position'])->toBe('bottom');

    $test->assertSee('cp-modal-shape-sheet')
        ->assertSee('cp-modal-sheet-handle');
});

it('renders sheet drag handlers and panel style binding', function (): void {
    Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', modalAttributes: [
            'type' => 'sheet',
        ])
        ->assertSee('x-on:pointermove.window="moveSheetDrag($event)"', false)
        ->assertSee('x-on:pointerup.window="endSheetDrag($event)"', false)
        ->assertSee('x-on:resize.window.debounce.120ms="handleViewportResize()"', false)
        ->assertSee('x-bind:style="panelStyle(', false)
        ->assertSee('const releaseY = this.eventClientY(event);', false)
        ->assertSee('startSheetResize(', false)
        ->assertSee('startSheetDrag(', false);
});

it('stores shared height attribute and preserves sheetHeight alias', function (): void {
    $heightTest = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', modalAttributes: [
            'height' => '65vh',
        ]);

    $heightStack = $heightTest->get('stack');
    $heightModals = $heightTest->get('modals');

    expect($heightModals[$heightStack[0]]['modalAttributes']['height'])->toBe('65vh');

    $sheetHeightAliasTest = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', modalAttributes: [
            'type' => 'sheet',
            'sheetHeight' => '74vh',
        ]);

    $sheetStack = $sheetHeightAliasTest->get('stack');
    $sheetModals = $sheetHeightAliasTest->get('modals');

    expect($sheetModals[$sheetStack[0]]['modalAttributes']['sheetHeight'])->toBe('74vh');
});

it('opens a bottom sheet through the openBottomSheet event alias', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openBottomSheet', component: 'test.example-modal');

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($stack)->toHaveCount(1);
    expect($modals[$stack[0]]['modalAttributes']['type'])->toBe('sheet');
});

it('normalizes invalid drawer position to right', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', modalAttributes: [
            'drawer' => true,
            'position' => 'top',
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['position'])->toBe('right');
});

it('stores non-drawer position for centered modal layout overrides', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', modalAttributes: [
            'position' => 'top',
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['drawer'])->toBeFalse();
    expect($modals[$stack[0]]['modalAttributes']['position'])->toBe('top');
});

it('forces drawer edge side to remain square in rendered classes', function (): void {
    Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', modalAttributes: [
            'drawer' => true,
            'position' => 'right',
            'class' => 'rounded-3xl',
        ])
        ->assertSee('rounded-3xl')
        ->assertSee('rounded-r-none');
});

it('keeps modalAttributes size when defined explicitly', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.attribute-sized-modal');

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['size'])->toBe('4xl');
});

it('handles click-away from overlay layer while preventing panel clicks from bubbling', function (): void {
    Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal')
        ->assertSee('cp-modal-livewire', false)
        ->assertSee('cp-modal-layer-backdrop', false)
        ->assertSee('x-on:click="closeOnClickAway()"', false)
        ->assertSee('x-on:click.stop', false);
});

it('stores isolate modal attribute and renders isolate visibility hooks', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', arguments: ['title' => 'Base'])
        ->dispatch('openModal', component: 'test.example-modal', arguments: ['title' => 'Overlay'], modalAttributes: [
            'isolate' => true,
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['isolate'])->toBeFalse();
    expect($modals[$stack[1]]['modalAttributes']['isolate'])->toBeTrue();

    $test->assertSee('x-show="shouldShowModal(', false)
        ->assertSee('cp-modal-layer-backdrop', false)
        ->assertSee('pointer-events-none', false);
});
