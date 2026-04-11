<?php

use Corepine\Modal\Actions\Action;
use Corepine\Modal\Enums\ModalType;
use Corepine\Modal\Support\ModalConfig;
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

    expect($actions[1]['type'])->toBe('method');
    expect($actions[1]['method'])->toBe('saveUsers');
    expect($actions[1]['params'])->toBe([42]);
});

it('supports fluent action helpers for colors, outlines, disabled state, and attributes', function (): void {
    $action = Action::make('saveUsers')
        ->label('Save')
        ->primary()
        ->outlined()
        ->disabled(fn (): bool => true)
        ->attributes(fn (): array => ['data-testid' => 'save-users'])
        ->action('saveUsers');

    $payload = $action->toArray();

    expect($payload['color'])->toBe('primary');
    expect($payload['outline'])->toBeTrue();
    expect($payload['disabled'])->toBeTrue();
    expect($payload['attributes'])->toMatchArray([
        'data-testid' => 'save-users',
    ]);
});
