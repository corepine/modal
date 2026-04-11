<?php

use Corepine\Modal\Actions\Action;
use Corepine\Modal\Livewire\ModalHost;
use Corepine\Modal\Tests\Fixtures\Livewire\AttributeSizedModal;
use Corepine\Modal\Tests\Fixtures\Livewire\ExampleModal;
use Corepine\Modal\Tests\Fixtures\Livewire\ManualLayoutModal;
use Corepine\Support\Colors\Color as SupportColor;
use Corepine\Support\Facades\CorepineColor;
use Livewire\Livewire;

beforeEach(function (): void {
    Livewire::component('test.example-modal', ExampleModal::class);
    Livewire::component('test.attribute-sized-modal', AttributeSizedModal::class);
    Livewire::component('test.manual-layout-modal', ManualLayoutModal::class);
});

it('opens and stacks modals', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal')
        ->dispatch('corepine-modal.open', component: 'test.example-modal', arguments: ['title' => 'Second']);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($stack)->toHaveCount(2);
    expect($test->get('activeModalId'))->toBe($stack[1]);
    expect($modals[$stack[1]]['arguments']['title'])->toBe('Second');
});

it('closes top modal layers', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal', arguments: ['title' => 'One'])
        ->dispatch('corepine-modal.open', component: 'test.example-modal', arguments: ['title' => 'Two'])
        ->dispatch('corepine-modal.open', component: 'test.example-modal', arguments: ['title' => 'Three']);

    $initialStack = $test->get('stack');

    $test->dispatch('corepine-modal.close-top', count: 2, destroy: true);

    $stack = $test->get('stack');

    expect($stack)->toHaveCount(1);
    expect($test->get('activeModalId'))->toBe($stack[0]);
    expect($stack[0])->toBe($initialStack[0]);
});

it('closes all modals when close-all is requested', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal')
        ->dispatch('corepine-modal.open', component: 'test.example-modal')
        ->dispatch('corepine-modal.close-top', closeAll: true);

    expect($test->get('stack'))->toBe([]);
    expect($test->get('modals'))->toBe([]);
    expect($test->get('activeModalId'))->toBeNull();
});

it('opens modal by class path', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: ExampleModal::class);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($stack)->toHaveCount(1);
    expect($modals[$stack[0]]['class'])->toBe(ExampleModal::class);
});

it('uses modalSize from component class', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal');

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['size'])->toBe('md');
});

it('allows runtime size override with raw classes', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal', modalAttributes: [
            'size' => 'max-w-[900px] sm:max-w-full',
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['size'])->toBe('max-w-[900px] sm:max-w-full');
});

it('stores runtime class and blur attributes', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal', modalAttributes: [
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
        ->dispatch('corepine-modal.open', component: 'test.example-modal', modalAttributes: [
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
        ->dispatch('corepine-modal.open', component: 'test.example-modal', modalAttributes: [
            'type' => 'sheet',
            'dismissible' => false,
            'showDragHandle' => true,
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['type'])->toBe('sheet');
    expect($modals[$stack[0]]['modalAttributes']['sheet'])->toBeTrue();
    expect($modals[$stack[0]]['modalAttributes']['drawer'])->toBeFalse();
    expect($modals[$stack[0]]['modalAttributes']['dismissible'])->toBeFalse();
    expect($modals[$stack[0]]['modalAttributes']['position'])->toBe('bottom');

    $test->assertSee('cp-modal-shape-sheet')
        ->assertSee('rounded-b-none')
        ->assertSee('cp-modal-sheet-handle');
});

it('renders sheet drag handlers and panel style binding', function (): void {
    Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal', modalAttributes: [
            'type' => 'sheet',
        ])
        ->assertSee('x-on:pointermove.window="moveSheetDrag($event)"', false)
        ->assertSee('x-on:pointerup.window="endSheetDrag($event)"', false)
        ->assertSee('x-on:resize.window.debounce.120ms="handleViewportResize()"', false)
        ->assertSee('x-bind:style="panelStyle(', false)
        ->assertSee('const releaseY = this.eventClientY(event);', false)
        ->assertSee('classHeightHint(value)', false)
        ->assertSee('const classPreferred = this.classHeightHint(attrs.class ?? \'\');', false)
        ->assertSee('const preferredSource = attrs.height ?? null;', false)
        ->assertSee('shouldShowSheetDragHandle(', false)
        ->assertSee('startSheetResize(', false)
        ->assertSee('startSheetDrag(', false);
});

it('applies explicit non-sheet height through panel style binding', function (): void {
    Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal', modalAttributes: [
            'type' => 'modal',
            'height' => '600px',
        ])
        ->assertSee('normalizePanelHeightValue(value, fallback = null)', false)
        ->assertSee('return this.nonSheetPanelStyle(id);', false)
        ->assertSee('const explicitHeight = this.normalizePanelHeightValue(attrs.height ?? null, null);', false);
});

it('stores shared height and max-height attributes', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal', modalAttributes: [
            'height' => '65vh',
            'maxHeight' => '90vh',
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['height'])->toBe('65vh');
    expect($modals[$stack[0]]['modalAttributes']['maxHeight'])->toBe('90vh');
});

it('opens a bottom sheet through the prefixed open-sheet event', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open-sheet', component: 'test.example-modal');

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($stack)->toHaveCount(1);
    expect($modals[$stack[0]]['modalAttributes']['type'])->toBe('sheet');
});

it('listens to configured open event aliases', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal')
        ->dispatch('openBottomSheet', component: 'test.example-modal');

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($stack)->toHaveCount(2);
    expect($modals[$stack[0]]['modalAttributes']['type'])->toBe('modal');
    expect($modals[$stack[1]]['modalAttributes']['type'])->toBe('sheet');
});

it('normalizes invalid drawer position to right', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal', modalAttributes: [
            'drawer' => true,
            'position' => 'top',
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['position'])->toBe('right');
});

it('stores non-drawer position for centered modal layout overrides', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal', modalAttributes: [
            'position' => 'top',
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['drawer'])->toBeFalse();
    expect($modals[$stack[0]]['modalAttributes']['position'])->toBe('top');
});

it('forces drawer edge side to remain square in rendered classes', function (): void {
    Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal', modalAttributes: [
            'drawer' => true,
            'position' => 'right',
            'class' => 'rounded-3xl',
        ])
        ->assertSee('rounded-3xl')
        ->assertSee('rounded-r-none');
});

it('keeps modalAttributes size when defined explicitly', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.attribute-sized-modal');

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['size'])->toBe('4xl');
});

it('handles click-away from overlay layer while preventing panel clicks from bubbling', function (): void {
    Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal')
        ->assertSee('cp-modal-livewire', false)
        ->assertSee('cp-modal-layer-backdrop', false)
        ->assertSee('x-on:click="if ($event.target === $event.currentTarget) handleClickAway()"', false)
        ->assertSee('x-on:click.stop', false);
});

it('stores isolate modal attribute and renders isolate visibility hooks', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal', arguments: ['title' => 'Base'])
        ->dispatch('corepine-modal.open', component: 'test.example-modal', arguments: ['title' => 'Overlay'], modalAttributes: [
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

it('renders automatic layout chrome and declarative footer actions', function (): void {
    Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal', modalAttributes: [
            'heading' => 'Manage Users',
            'description' => 'Search and view users in your system.',
            'footerActionsAlignment' => 'center',
            'footerActions' => [
                ['type' => 'close', 'label' => 'Cancel'],
                ['type' => 'method', 'method' => 'saveUsers', 'label' => 'Save', 'class' => 'rounded-md bg-zinc-900 px-3 py-2 text-sm text-white'],
            ],
        ])
        ->assertSee('cp-modal-layout')
        ->assertSee('Manage Users')
        ->assertSee('Search and view users in your system.')
        ->assertSee('Cancel')
        ->assertSee('Save')
        ->assertSee('justify-center')
        ->assertSee('callModalMethod(', false)
        ->assertSee('saveUsers', false);
});

it('supports fluent Action objects inside footerActions', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal', modalAttributes: [
            'heading' => 'Manage Users',
            'footerActions' => [
                Action::make('cancel')
                    ->label('Cancel')
                    ->class('rounded-md border px-3 py-2 text-sm')
                    ->close(),
                Action::make('saveUsers')
                    ->label('Save')
                    ->class('rounded-md bg-zinc-900 px-3 py-2 text-sm text-white')
                    ->action('saveUsers', [5]),
            ],
        ])
        ->assertSee('Cancel')
        ->assertSee('Save');

    $stack = $test->get('stack');
    $modals = $test->get('modals');
    $actions = $modals[$stack[0]]['modalAttributes']['footerActions'] ?? [];

    expect($actions)->toHaveCount(2);
    expect($actions[0]['type'])->toBe('close');
    expect($actions[1]['type'])->toBe('method');
    expect($actions[1]['method'])->toBe('saveUsers');
});

it('resolves support colors and richer action options inside footerActions', function (): void {
    CorepineColor::register([
        'brand' => SupportColor::Fuchsia,
    ]);

    $test = Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal', modalAttributes: [
            'heading' => 'Manage Users',
            'footerActions' => [
                Action::make('cancel')
                    ->label('Cancel')
                    ->color('purple')
                    ->outline()
                    ->attributes(['data-testid' => 'cancel-action'])
                    ->close(),
                Action::make('saveUsers')
                    ->label('Save')
                    ->color(fn () => 'brand')
                    ->disabled(fn () => true)
                    ->attributes(fn (): array => ['data-testid' => 'save-action'])
                    ->action('saveUsers', [5]),
            ],
        ])
        ->assertSee('data-testid="cancel-action"', false)
        ->assertSee('data-testid="save-action"', false)
        ->assertSee('disabled', false);

    $stack = $test->get('stack');
    $modals = $test->get('modals');
    $actions = $modals[$stack[0]]['modalAttributes']['footerActions'] ?? [];

    expect($actions)->toHaveCount(2);
    expect($actions[0]['class'])->toContain('cp-modal-action-outline');
    expect($actions[0]['style'])->toContain(SupportColor::Purple[700]);
    expect($actions[0]['attributes'])->toMatchArray(['data-testid' => 'cancel-action']);
    expect($actions[1]['disabled'])->toBeTrue();
    expect($actions[1]['class'])->toContain('cp-modal-action-solid');
    expect($actions[1]['style'])->toContain(SupportColor::Fuchsia[500]);
    expect($actions[1]['attributes'])->toMatchArray(['data-testid' => 'save-action']);
});

it('supports raw footer action arrays with color and outline options', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal', modalAttributes: [
            'heading' => 'Manage Users',
            'footerActions' => [
                [
                    'type' => 'method',
                    'method' => 'saveUsers',
                    'label' => 'Review',
                    'color' => 'rose',
                    'outline' => true,
                    'attributes' => [
                        'data-testid' => 'review-action',
                    ],
                ],
            ],
        ])
        ->assertSee('data-testid="review-action"', false);

    $stack = $test->get('stack');
    $modals = $test->get('modals');
    $action = $modals[$stack[0]]['modalAttributes']['footerActions'][0] ?? [];

    expect($action['class'])->toContain('cp-modal-action-outline');
    expect($action['style'])->toContain(SupportColor::Rose[700]);
    expect($action['attributes'])->toMatchArray(['data-testid' => 'review-action']);
});

it('supports disabling automatic shell with shell attribute', function (): void {
    Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.example-modal', modalAttributes: [
            'shell' => false,
            'heading' => 'Should not render chrome',
        ])
        ->assertDontSee('cp-modal-layout');
});

it('can keep manual layout footer visible while stacking when shell mode is disabled', function (): void {
    Livewire::test(ModalHost::class)
        ->dispatch('corepine-modal.open', component: 'test.manual-layout-modal', modalAttributes: [
            'shell' => false,
        ])
        ->assertSee('Manual Footer')
        ->dispatch('corepine-modal.open', component: 'test.manual-layout-modal', modalAttributes: [
            'shell' => false,
        ])
        ->assertSee('Manual Footer');
});
