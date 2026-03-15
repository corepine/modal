<?php

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
