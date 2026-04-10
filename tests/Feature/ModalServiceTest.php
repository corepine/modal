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

it('reads customized event names through modal facade', function (): void {
    config()->set('corepine-modal.events.listen.open_sheet', ['corepine-modal.custom.open-sheet', 'fallback-sheet']);
    config()->set('corepine-modal.events.listen.close', 'corepine-modal.custom.close');

    expect(ModalFacade::event()->openBottomSheet())->toBe('corepine-modal.custom.open-sheet');
    expect(ModalFacade::event()->closeModal())->toBe('corepine-modal.custom.close');
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
