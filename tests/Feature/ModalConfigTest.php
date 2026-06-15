<?php

use Corepine\Modal\Actions\Action;
use Corepine\Modal\Enums\ModalType;
use Corepine\Modal\Support\ModalConfig;
use Corepine\Support\Colors\Color as SupportColor;
use Corepine\Support\Facades\CorepineColor;
use Corepine\Support\Enums\Placement;
use Corepine\Support\Enums\Alignment;

it('normalizes drawer positions to left or right', function (): void {
    $config = app(ModalConfig::class);

    expect($config->modalPlacement(['drawer' => true, 'placement' => 'left']))->toBe('left');
    expect($config->modalPlacement(['drawer' => true, 'placement' => 'right']))->toBe('right');
    expect($config->modalPlacement(['drawer' => true, 'placement' => 'top']))->toBe('right');
    expect($config->modalPlacement(['drawer' => true, 'placement' => 'center']))->toBe('right');
});

it('forces sheet positions to bottom', function (): void {
    $config = app(ModalConfig::class);

    expect($config->modalPlacement(['type' => 'sheet']))->toBe('bottom');
    expect($config->modalPlacement(['type' => 'sheet', 'placement' => 'top']))->toBe('bottom');
    expect($config->modalPlacement(['bottomSheet' => true, 'placement' => 'left']))->toBe('bottom');
});

it('normalizes standard modal positions', function (): void {
    $config = app(ModalConfig::class);

    expect($config->modalPlacement(['drawer' => false, 'placement' => 'top']))->toBe('top');
    expect($config->modalPlacement(['drawer' => false, 'placement' => 'bottom']))->toBe('bottom');
    expect($config->modalPlacement(['drawer' => false, 'placement' => 'left']))->toBe('left');
    expect($config->modalPlacement(['drawer' => false, 'placement' => 'right']))->toBe('right');
    expect($config->modalPlacement(['drawer' => false, 'placement' => 'invalid']))->toBe('center');
});

it('normalizes modal origins by modal type', function (): void {
    $config = app(ModalConfig::class);

    expect($config->modalOrigin(['type' => 'sheet', 'origin' => 'left']))->toBe('bottom');
    expect($config->modalOrigin(['drawer' => true, 'placement' => 'left', 'origin' => 'top']))->toBe('left');
    expect($config->modalOrigin(['type' => 'modal', 'placement' => 'right']))->toBe('right');
    expect($config->modalOrigin(['type' => 'modal', 'placement' => 'right', 'origin' => 'left']))->toBe('left');
    expect($config->modalOrigin(['type' => 'modal', 'origin' => 'invalid']))->toBe('center');
    expect($config->modalPlacement(['type' => 'modal', 'placement' => Placement::Left]))->toBe('left');
    expect($config->modalOrigin(['type' => 'modal', 'origin' => Placement::Bottom]))->toBe('bottom');
    expect($config->modalOriginClass(['type' => 'modal', 'origin' => 'top']))->toBe('origin-top');
});

it('uses placement-aware transitions for standard modals', function (): void {
    $config = app(ModalConfig::class);

    expect($config->modalTransitionClasses(['type' => 'modal', 'placement' => 'left'])['enterStart'])->toContain('-translate-x-6');
    expect($config->modalTransitionClasses(['type' => 'modal', 'placement' => 'right'])['enterStart'])->toContain('translate-x-6');
    expect($config->modalTransitionClasses(['type' => 'modal', 'placement' => 'top'])['enterStart'])->toContain('-translate-y-6');
    expect($config->modalTransitionClasses(['type' => 'modal', 'placement' => 'bottom'])['enterStart'])->toContain('translate-y-6');
    expect($config->modalTransitionClasses([
        'type' => 'modal',
        'placement' => 'bottom',
        'origin' => 'right',
    ])['enterStart'])->toContain('translate-x-6');
});

it('keeps built-in size tokens while allowing config overrides', function (): void {
    config()->set('corepine-modal.sizes', [
        'default' => 'max-w-md sm:max-w-full',
        'editor' => 'max-w-[900px]',
    ]);

    $config = app(ModalConfig::class);
    $sizes = $config->sizes();

    expect($sizes['default'])->toBe('max-w-md sm:max-w-full');
    expect($sizes['editor'])->toBe('max-w-[900px]');
    expect($sizes['3xl'])->toBe('max-w-3xl');
    expect($sizes['7xl'])->toBe('max-w-7xl');
    expect($config->modalSizeClasses(['size' => '3xl']))->toBe('max-w-3xl');
});

it('normalizes isolate attribute to boolean', function (): void {
    $config = app(ModalConfig::class);

    expect($config->mergedModalAttributes([], ['isolate' => 'true'])['isolate'])->toBeTrue();
    expect($config->mergedModalAttributes([], ['isolate' => '1'])['isolate'])->toBeTrue();
    expect($config->mergedModalAttributes([], ['isolate' => false])['isolate'])->toBeFalse();
});

it('normalizes modal type using enum, explicit type, and helper flags', function (): void {
    $config = app(ModalConfig::class);

    expect($config->modalType(['type' => ModalType::Drawer]))->toBe('drawer');
    expect($config->modalType(['type' => 'sheet']))->toBe('sheet');
    expect($config->modalType(['type' => 'bottomSheet']))->toBe('sheet');
    expect($config->modalType(['type' => 'modal']))->toBe('modal');
    expect($config->modalType(['drawer' => true]))->toBe('drawer');
    expect($config->modalType(['sheet' => true]))->toBe('sheet');
    expect($config->modalType(['bottomSheet' => true]))->toBe('sheet');

    $normalizedSheet = $config->mergedModalAttributes([], ['type' => 'sheet']);
    $normalizedBottomSheet = $config->mergedModalAttributes([], [
        'bottomSheet' => true,
        'dismissible' => false,
        'closeAllOnEscape' => true,
        'enableDrag' => true,
        'showDragHandle' => false,
    ]);
    $normalizedDrawer = $config->mergedModalAttributes([], ['drawer' => true]);

    expect($normalizedSheet['type'])->toBe('sheet');
    expect($normalizedSheet['sheet'])->toBeTrue();
    expect($normalizedSheet['drawer'])->toBeFalse();
    expect($normalizedSheet['placement'])->toBe('bottom');

    expect($normalizedBottomSheet['type'])->toBe('sheet');
    expect($normalizedBottomSheet['sheet'])->toBeTrue();
    expect($normalizedBottomSheet['dismissible'])->toBeFalse();
    expect($normalizedBottomSheet['closeAllOnEscape'])->toBeTrue();
    expect($normalizedBottomSheet['draggable'])->toBeTrue();
    expect($normalizedBottomSheet['showDragHandle'])->toBeFalse();

    expect($normalizedDrawer['type'])->toBe('drawer');
    expect($normalizedDrawer['drawer'])->toBeTrue();
    expect($normalizedDrawer['sheet'])->toBeFalse();
    expect($normalizedDrawer['placement'])->toBe('right');
});

it('supports shell attributes', function (): void {
    $config = app(ModalConfig::class);

    expect($config->usesLayout(['shell' => true]))->toBeTrue();
    expect($config->usesLayout(['shell' => false]))->toBeFalse();
    expect($config->layoutHeading(['heading' => 'Users']))->toBe('Users');
    expect($config->layoutDescription(['description' => 'Search and view users']))->toBe('Search and view users');
    expect($config->layoutShowClose([]))->toBeFalse();
    expect($config->layoutShowClose(['heading' => 'Users']))->toBeTrue();
    expect($config->layoutShowClose(['showClose' => 'false']))->toBeFalse();
    expect($config->layoutFooterActionsAlignment(['footerActionsAlignment' => 'center']))->toBe('center');
    expect($config->layoutFooterActionsAlignment(['footerActionsAlignment' => Alignment::Right]))->toBe('end');
    expect($config->layoutFooterActionsAlignmentClass(['footerActionsAlignment' => 'start']))->toBe('justify-start');
});

it('uses 0.5 as the default sheet drag close threshold', function (): void {
    $config = app(ModalConfig::class);

    expect($config->mergedModalAttributes([], ['type' => 'sheet'])['dragCloseThreshold'])->toBe(0.5);
});

it('normalizes declarative footer actions for auto layout', function (): void {
    $config = app(ModalConfig::class);

    $actions = $config->layoutFooterActions([
        'actions' => [
            ['type' => 'close', 'label' => 'Cancel', 'layers' => 2, 'destroy' => false],
            ['type' => 'method', 'method' => 'saveUsers', 'params' => [5], 'label' => 'Save'],
            'refreshList',
        ],
    ]);

    expect($actions)->toHaveCount(3);
    expect($actions[0]['type'])->toBe('close');
    expect($actions[0]['label'])->toBe('Cancel');
    expect($actions[0]['layers'])->toBe(2);
    expect($actions[0]['destroy'])->toBeFalse();

    expect($actions[1]['type'])->toBe('method');
    expect($actions[1]['method'])->toBe('saveUsers');
    expect($actions[1]['params'])->toBe([5]);
    expect($actions[1]['class'])->toContain('inline-flex min-h-10');
    expect($actions[1]['class'])->toContain('border-gray-200');
    expect($actions[1]['class'])->toContain('text-gray-700');
    expect($actions[1]['style'])->toBe('');

    expect($actions[2]['type'])->toBe('method');
    expect($actions[2]['method'])->toBe('refreshList');
});

it('normalizes fluent Action objects for auto layout footer actions', function (): void {
    $config = app(ModalConfig::class);

    $actions = $config->layoutFooterActions([
        'actions' => [
            Action::make('cancel')
                ->label('Cancel')
                ->class('rounded-md border px-3 py-2 text-sm')
                ->close(),
            Action::make('saveUsers')
                ->label('Save')
                ->class('rounded-md bg-zinc-900 px-3 py-2 text-sm text-white')
                ->action('saveUsers', [42]),
        ],
    ]);

    expect($actions)->toHaveCount(2);
    expect($actions[0]['type'])->toBe('close');
    expect($actions[0]['label'])->toBe('Cancel');
    expect($actions[0]['destroy'])->toBeTrue();
    expect($actions[0]['class'])->toContain('inline-flex min-h-10');

    expect($actions[1]['type'])->toBe('method');
    expect($actions[1]['method'])->toBe('saveUsers');
    expect($actions[1]['params'])->toBe([42]);
    expect($actions[1]['class'])->toContain('inline-flex min-h-10');
});

it('supports fluent action helpers for colors, outlines, disabled state, and attributes', function (): void {
    CorepineColor::flush();
    CorepineColor::register([
        'primary' => SupportColor::Amber,
    ]);

    $action = Action::make('saveUsers')
        ->label('Save')
        ->primary()
        ->outlined()
        ->disabled(fn (): bool => true)
        ->attributes(fn (): array => ['data-testid' => 'save-users'])
        ->action('saveUsers');

    $payload = $action->toArray();

    expect($payload['resolved'])->toBeTrue();
    expect($payload['color'])->toMatchArray(SupportColor::Amber);
    expect($payload['class'])->toContain('bg-transparent');
    expect($payload['class'])->toContain('border-amber-200');
    expect($payload['class'])->toContain('pointer-events-none');
    expect($payload['class'])->toContain('cursor-not-allowed');
    expect($payload['style'])->toBe('');
    expect($payload['outline'])->toBeTrue();
    expect($payload['disabled'])->toBeTrue();
    expect($payload['attributes'])->toMatchArray([
        'data-testid' => 'save-users',
    ]);
});

it('supports post-close dispatch payloads on fluent close actions', function (): void {
    $payload = Action::make('cancel')
        ->label('Cancel')
        ->dispatch([
            'users-refreshed' => ['user' => 5],
        ])
        ->dispatchTo([
            'test.example-modal' => [
                'focus-user' => ['user' => 5],
            ],
        ])
        ->close()
        ->toArray();

    expect($payload['type'])->toBe('close');
    expect($payload['dispatch'])->toBe([
        'users-refreshed' => ['user' => 5],
    ]);
    expect($payload['dispatchTo'])->toBe([
        'test.example-modal' => [
            'focus-user' => ['user' => 5],
        ],
    ]);
});

it('treats dispatch-only fluent actions as dispatch actions', function (): void {
    $payload = Action::make('users')
        ->label('Users')
        ->dispatch('modal.open', ['component' => 'users'])
        ->toArray();

    expect($payload['type'])->toBe('dispatch');
    expect($payload['event'])->toBe('modal.open');
    expect($payload['payload'])->toBe([
        'component' => 'users',
    ]);
    expect($payload)->not->toHaveKey('method');
});

it('treats dispatch-to fluent actions as dispatchTo actions', function (): void {
    $payload = Action::make('focusUsers')
        ->label('Focus Users')
        ->dispatchTo('orders.table', 'sync-user', ['user' => 5])
        ->toArray();

    expect($payload['type'])->toBe('dispatchTo');
    expect($payload['target'])->toBe('orders.table');
    expect($payload['event'])->toBe('sync-user');
    expect($payload['payload'])->toBe([
        'user' => 5,
    ]);
    expect($payload)->not->toHaveKey('method');
});

it('supports plain fluent buttons with explicit html button types', function (): void {
    $payload = Action::make('submitForm')
        ->label('Submit')
        ->type('submit')
        ->toArray();

    expect($payload['type'])->toBe('button');
    expect($payload['buttonType'])->toBe('submit');
    expect($payload)->not->toHaveKey('method');
});

it('supports accent action colors as softer defaults', function (): void {
    $payload = Action::make('saveUsers')
        ->label('Save')
        ->danger()
        ->accent()
        ->action('saveUsers')
        ->toArray();

    expect($payload['accent'])->toBeTrue();
        expect($payload['class'])->toContain('!bg-red-50');
        expect($payload['class'])->toContain('hover:!bg-red-100');
        expect($payload['class'])->toContain('dark:!bg-red-950');
    expect($payload['class'])->toContain('!text-red-700');
    expect($payload['style'])->toBe('');
});

it('marks disabled fluent actions as non-interactive', function (): void {
    $payload = Action::make('saveUsers')
        ->label('Save')
        ->danger()
        ->disabled()
        ->action('saveUsers')
        ->toArray();

    expect($payload['disabled'])->toBeTrue();
    expect($payload['class'])->toContain('pointer-events-none');
    expect($payload['class'])->toContain('cursor-not-allowed');
});

it('omits invisible actions from auto layout output', function (): void {
    $config = app(ModalConfig::class);

    $actions = $config->layoutFooterActions([
        'actions' => [
            Action::make('hidden')
                ->label('Hidden')
                ->visible(false)
                ->action('hiddenAction'),
            Action::make('save')
                ->label('Save')
                ->action('saveAction'),
        ],
    ]);

    expect($actions)->toHaveCount(1);
    expect($actions[0]['method'])->toBe('saveAction');
});

it('resolves semantic action color aliases without explicit registration', function (): void {
    $config = app(ModalConfig::class);

    $actions = $config->layoutFooterActions([
        'actions' => [
            Action::make('save')
                ->label('Save')
                ->primary()
                ->action('saveUsers'),
            Action::make('delete')
                ->label('Delete')
                ->danger()
                ->action('deleteUsers'),
            Action::make('warn')
                ->label('Warn')
                ->warning()
                ->outline()
                ->action('warnUsers'),
        ],
    ]);

    expect($actions[0]['class'])->toContain('bg-blue-600');
    expect($actions[0]['class'])->toContain('hover:bg-blue-500');
    expect($actions[0]['style'])->toBe('');
    expect($actions[1]['class'])->toContain('bg-red-600');
    expect($actions[1]['class'])->toContain('hover:bg-red-500');
    expect($actions[1]['style'])->toBe('');
    expect($actions[2]['class'])->toContain('border-yellow-200');
    expect($actions[2]['class'])->toContain('hover:bg-yellow-50');
    expect($actions[2]['style'])->toBe('');
});
