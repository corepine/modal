<?php

use Corepine\Modal\Enums\ModalType;
use Corepine\Modal\Support\ModalConfig;

it('normalizes drawer positions to left or right', function (): void {
    $config = app(ModalConfig::class);

    expect($config->modalPosition(['drawer' => true, 'position' => 'left']))->toBe('left');
    expect($config->modalPosition(['drawer' => true, 'position' => 'right']))->toBe('right');
    expect($config->modalPosition(['drawer' => true, 'position' => 'top']))->toBe('right');
    expect($config->modalPosition(['drawer' => true, 'position' => 'center']))->toBe('right');
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

it('normalizes isolate attribute to boolean and supports legacy isolated alias', function (): void {
    $config = app(ModalConfig::class);

    expect($config->mergedModalAttributes([], ['isolate' => 'true'])['isolate'])->toBeTrue();
    expect($config->mergedModalAttributes([], ['isolate' => '1'])['isolate'])->toBeTrue();
    expect($config->mergedModalAttributes([], ['isolate' => false])['isolate'])->toBeFalse();

    expect($config->mergedModalAttributes([], ['isolated' => true])['isolate'])->toBeTrue();
});

it('normalizes modal type using enum, explicit type, and legacy flags', function (): void {
    $config = app(ModalConfig::class);

    expect($config->modalType(['type' => ModalType::Drawer]))->toBe('drawer');
    expect($config->modalType(['type' => 'sheet']))->toBe('sheet');
    expect($config->modalType(['type' => 'modal']))->toBe('modal');
    expect($config->modalType(['drawer' => true]))->toBe('drawer');
    expect($config->modalType(['sheet' => true]))->toBe('sheet');

    $normalizedSheet = $config->mergedModalAttributes([], ['type' => 'sheet']);
    $normalizedDrawer = $config->mergedModalAttributes([], ['drawer' => true]);

    expect($normalizedSheet['type'])->toBe('sheet');
    expect($normalizedSheet['sheet'])->toBeTrue();
    expect($normalizedSheet['drawer'])->toBeFalse();
    expect($normalizedSheet['position'])->toBe('bottom');

    expect($normalizedDrawer['type'])->toBe('drawer');
    expect($normalizedDrawer['drawer'])->toBeTrue();
    expect($normalizedDrawer['sheet'])->toBeFalse();
    expect($normalizedDrawer['position'])->toBe('right');
});
