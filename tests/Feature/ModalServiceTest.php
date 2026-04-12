<?php

use Corepine\Modal\Facades\Modal as ModalFacade;
use Corepine\Modal\ModalService;
use Corepine\Modal\Support\ModalConfig;

it('resolves listen events through modal service', function (): void {
    $service = app(ModalService::class);
    $events = $service->event();

    expect($events->openModal())->toBe('modal.open');
    expect($events->openBottomSheet())->toBe('modal.open-sheet');
    expect($events->closeModal())->toBe('modal.close');
    expect($events->closeTopModal())->toBe('modal.close-top');
    expect($events->closeAllModals())->toBe('modal.close-all');
    expect($events->destroyModal())->toBe('modal.destroy');
    expect($events->resetModal())->toBe('modal.reset');
    expect($events->all())->toMatchArray([
        'open' => 'modal.open',
        'open_sheet' => 'modal.open-sheet',
        'close' => 'modal.close',
        'close_top' => 'modal.close-top',
        'close_all' => 'modal.close-all',
        'destroy' => 'modal.destroy',
        'reset' => 'modal.reset',
    ]);
});

it('resolves listen events from config and allows overrides', function (): void {
    $config = app(ModalConfig::class);

    expect($config->listenEvents('open'))->toBe(['modal.open']);
    expect($config->listenEvents('open_sheet'))->toBe(['modal.open-sheet']);
    expect($config->listenEvents('close'))->toBe(['modal.close']);
    expect($config->listenEvents('close_top'))->toBe(['modal.close-top']);
    expect($config->listenEvents('close_all'))->toBe(['modal.close-all']);

    config()->set('corepine-modal.events.listen.open', 'acme.modal.open');
    config()->set('corepine-modal.events.listen.close', 'acme.modal.close');

    expect($config->listenEvent('open'))->toBe('acme.modal.open');
    expect($config->listenEvent('close'))->toBe('acme.modal.close');
    expect(app(ModalService::class)->event()->openModal())->toBe('acme.modal.open');
    expect(app(ModalService::class)->event()->closeModal())->toBe('acme.modal.close');
});

it('defaults outgoing modal events to prefixed names and respects overrides', function (): void {
    $config = app(ModalConfig::class);

    expect($config->dispatchEvent('opened'))->toBe('modal.opened');
    expect($config->dispatchEvent('closed'))->toBe('modal.closed');
    expect($config->dispatchEvent('changed'))->toBe('modal.changed');
    expect($config->dispatchEvent('all_closed'))->toBe('modal.all-closed');
    expect($config->dispatchEvent('component_closed'))->toBe('modal.component-closed');

    config()->set('corepine-modal.events.dispatch.closed', 'modal.custom.closed');

    expect($config->dispatchEvent('closed'))->toBe('modal.custom.closed');
});

it('supports resolving modal facade class through app helper for blade compatibility', function (): void {
    $service = app(\Corepine\Modal\Facades\Modal::class);

    expect($service)->toBeInstanceOf(ModalService::class);
    expect($service->event()->openBottomSheet())->toBe('modal.open-sheet');
});
