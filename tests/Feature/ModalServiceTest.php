<?php

use Corepine\Modal\Facades\Modal as ModalFacade;
use Corepine\Modal\ModalService;
use Corepine\Modal\Support\ModalConfig;

it('resolves listen events through modal service', function (): void {
    $service = app(ModalService::class);
    $events = $service->event();

    expect($events->openModal())->toBe('corepine-modal.open');
    expect($events->openBottomSheet())->toBe('corepine-modal.open-sheet');
    expect($events->closeModal())->toBe('corepine-modal.close');
    expect($events->closeTopModal())->toBe('corepine-modal.close-top');
    expect($events->closeAllModals())->toBe('corepine-modal.close-all');
    expect($events->destroyModal())->toBe('corepine-modal.destroy');
    expect($events->resetModal())->toBe('corepine-modal.reset');
    expect($events->all())->toMatchArray([
        'open' => 'corepine-modal.open',
        'open_sheet' => 'corepine-modal.open-sheet',
        'close' => 'corepine-modal.close',
        'close_top' => 'corepine-modal.close-top',
        'close_all' => 'corepine-modal.close-all',
        'destroy' => 'corepine-modal.destroy',
        'reset' => 'corepine-modal.reset',
    ]);
});

it('exposes only the fixed namespaced listen events', function (): void {
    $config = app(ModalConfig::class);

    expect($config->listenEvents('open'))->toBe(['corepine-modal.open']);
    expect($config->listenEvents('open_sheet'))->toBe(['corepine-modal.open-sheet']);
    expect($config->listenEvents('close'))->toBe(['corepine-modal.close']);
    expect($config->listenEvents('close_top'))->toBe(['corepine-modal.close-top']);
    expect($config->listenEvents('close_all'))->toBe(['corepine-modal.close-all']);
});

it('defaults outgoing modal events to prefixed names and respects overrides', function (): void {
    $config = app(ModalConfig::class);

    expect($config->dispatchEvent('opened'))->toBe('corepine-modal.opened');
    expect($config->dispatchEvent('closed'))->toBe('corepine-modal.closed');
    expect($config->dispatchEvent('changed'))->toBe('corepine-modal.changed');
    expect($config->dispatchEvent('all_closed'))->toBe('corepine-modal.all-closed');
    expect($config->dispatchEvent('component_closed'))->toBe('corepine-modal.component-closed');

    config()->set('corepine-modal.events.dispatch.closed', 'corepine-modal.custom.closed');

    expect($config->dispatchEvent('closed'))->toBe('corepine-modal.custom.closed');
});

it('supports resolving modal facade class through app helper for blade compatibility', function (): void {
    $service = app(\Corepine\Modal\Facades\Modal::class);

    expect($service)->toBeInstanceOf(ModalService::class);
    expect($service->event()->openBottomSheet())->toBe('corepine-modal.open-sheet');
});
