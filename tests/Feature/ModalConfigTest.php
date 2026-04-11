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

    expect($config->modalPosition(['drawer' => true, 'position' => 'left']))->toBe('left');
    expect($config->modalPosition(['drawer' => true, 'position' => 'right']))->toBe('right');
    expect($config->modalPosition(['drawer' => true, 'position' => 'top']))->toBe('right');
    expect($config->modalPosition(['drawer' => true, 'position' => 'center']))->toBe('right');
});

it('forces sheet positions to bottom', function (): void {
    $config = app(ModalConfig::class);

    expect($config->modalPosition(['type' => 'sheet']))->toBe('bottom');
    expect($config->modalPosition(['type' => 'sheet', 'position' => 'top']))->toBe('bottom');
    expect($config->modalPosition(['bottomSheet' => true, 'position' => 'left']))->toBe('bottom');
});

it('normalizes standard modal positions', function (): void {
    $config = app(ModalConfig::class);

    expect($config->modalPosition(['drawer' => false, 'position' => 'top']))->toBe('top');
    expect($config->modalPosition(['drawer' => false, 'position' => 'bottom']))->toBe('bottom');
    expect($config->modalPosition(['drawer' => false, 'position' => 'left']))->toBe('left');
    expect($config->modalPosition(['drawer' => false, 'position' => 'right']))->toBe('right');
    expect($config->modalPosition(['drawer' => false, 'position' => 'invalid']))->toBe('center');
});

it('normalizes modal origins by modal type', function (): void {
    $config = app(ModalConfig::class);

    expect($config->modalOrigin(['type' => 'sheet', 'origin' => 'left']))->toBe('bottom');
    expect($config->modalOrigin(['drawer' => true, 'position' => 'left', 'origin' => 'top']))->toBe('left');
    expect($config->modalOrigin(['type' => 'modal', 'position' => 'right']))->toBe('right');
    expect($config->modalOrigin(['type' => 'modal', 'position' => 'right', 'origin' => 'left']))->toBe('left');
    expect($config->modalOrigin(['type' => 'modal', 'origin' => 'invalid']))->toBe('center');
    expect($config->modalPosition(['type' => 'modal', 'position' => Placement::Left]))->toBe('left');
    expect($config->modalOrigin(['type' => 'modal', 'origin' => Placement::Bottom]))->toBe('bottom');
    expect($config->modalOriginClass(['type' => 'modal', 'origin' => 'top']))->toBe('origin-top');
});

it('uses position-aware transitions for standard modals', function (): void {
    $config = app(ModalConfig::class);

    expect($config->modalTransitionClasses(['type' => 'modal', 'position' => 'left'])['enterStart'])->toContain('-translate-x-6');
    expect($config->modalTransitionClasses(['type' => 'modal', 'position' => 'right'])['enterStart'])->toContain('translate-x-6');
    expect($config->modalTransitionClasses(['type' => 'modal', 'position' => 'top'])['enterStart'])->toContain('-translate-y-6');
    expect($config->modalTransitionClasses(['type' => 'modal', 'position' => 'bottom'])['enterStart'])->toContain('translate-y-6');
    expect($config->modalTransitionClasses([
        'type' => 'modal',
        'position' => 'bottom',
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
    expect($normalizedSheet['position'])->toBe('bottom');

    expect($normalizedBottomSheet['type'])->toBe('sheet');
    expect($normalizedBottomSheet['sheet'])->toBeTrue();
    expect($normalizedBottomSheet['dismissible'])->toBeFalse();
    expect($normalizedBottomSheet['closeAllOnEscape'])->toBeTrue();
    expect($normalizedBottomSheet['draggable'])->toBeTrue();
    expect($normalizedBottomSheet['showDragHandle'])->toBeFalse();

    expect($normalizedDrawer['type'])->toBe('drawer');
    expect($normalizedDrawer['drawer'])->toBeTrue();
    expect($normalizedDrawer['sheet'])->toBeFalse();
    expect($normalizedDrawer['position'])->toBe('right');
});

it('supports shell attributes', function (): void {
    $config = app(ModalConfig::class);

    expect($config->usesLayout(['shell' => true]))->toBeTrue();
    expect($config->usesLayout(['shell' => false]))->toBeFalse();
    expect($config->layoutHeading(['heading' => 'Users']))->toBe('Users');
    expect($config->layoutDescription(['description' => 'Search and view users']))->toBe('Search and view users');
    expect($config->layoutShowClose(['showClose' => 'false']))->toBeFalse();
    expect($config->layoutFooterActionsAlignment(['footerActionsAlignment' => 'center']))->toBe('center');
    expect($config->layoutFooterActionsAlignment(['footerActionsAlignment' => Alignment::Right]))->toBe('end');
    expect($config->layoutFooterActionsAlignmentClass(['footerActionsAlignment' => 'start']))->toBe('justify-start');
});

it('normalizes declarative footer actions for auto layout', function (): void {
    $config = app(ModalConfig::class);

    $actions = $config->layoutFooterActions([
        'footerActions' => [
            ['type' => 'close', 'label' => 'Cancel', 'count' => 2, 'destroy' => false],
            ['type' => 'method', 'method' => 'saveUsers', 'params' => [5], 'label' => 'Save'],
            'refreshList',
        ],
    ]);

    expect($actions)->toHaveCount(3);
    expect($actions[0]['type'])->toBe('close');
    expect($actions[0]['label'])->toBe('Cancel');
    expect($actions[0]['count'])->toBe(2);
    expect($actions[0]['destroy'])->toBeFalse();

    expect($actions[1]['type'])->toBe('method');
    expect($actions[1]['method'])->toBe('saveUsers');
    expect($actions[1]['params'])->toBe([5]);
    expect($actions[1]['class'])->toContain('cp-modal-action-outline');
    expect($actions[1]['style'])->toContain(SupportColor::Gray[700]);

    expect($actions[2]['type'])->toBe('method');
    expect($actions[2]['method'])->toBe('refreshList');
});

it('normalizes fluent Action objects for auto layout footer actions', function (): void {
    $config = app(ModalConfig::class);

    $actions = $config->layoutFooterActions([
        'footerActions' => [
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
    expect($actions[0]['class'])->toContain('cp-modal-action');

    expect($actions[1]['type'])->toBe('method');
    expect($actions[1]['method'])->toBe('saveUsers');
    expect($actions[1]['params'])->toBe([42]);
    expect($actions[1]['class'])->toContain('cp-modal-action');
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
    expect($payload['class'])->toContain('hover:bg-amber-50');
    expect($payload['style'])->toBe('');
    expect($payload['outline'])->toBeTrue();
    expect($payload['disabled'])->toBeTrue();
    expect($payload['attributes'])->toMatchArray([
        'data-testid' => 'save-users',
    ]);
});

it('supports accent action colors as softer defaults', function (): void {
    $payload = Action::make('saveUsers')
        ->label('Save')
        ->danger()
        ->accent()
        ->action('saveUsers')
        ->toArray();

    expect($payload['accent'])->toBeTrue();
    expect($payload['class'])->toContain('!bg-red-100');
    expect($payload['class'])->toContain('hover:!bg-red-200');
    expect($payload['class'])->toContain('!text-red-700');
    expect($payload['style'])->toBe('');
});

it('resolves semantic action color aliases without explicit registration', function (): void {
    $config = app(ModalConfig::class);

    $actions = $config->layoutFooterActions([
        'footerActions' => [
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
