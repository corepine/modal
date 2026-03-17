<?php

use Corepine\Modal\Facades\Modal as ModalFacade;
use Corepine\Modal\ModalService;

it('resolves listen events through modal service', function (): void {
    $service = app(ModalService::class);
    $events = $service->event();

    expect($events->openModal())->toBe('openModal');
    expect($events->openBottomSheet())->toBe('openBottomSheet');
    expect($events->closeModal())->toBe('closeModal');
    expect($events->closeTopModal())->toBe('closeTopModal');
    expect($events->closeAllModals())->toBe('closeAllModals');
    expect($events->destroyModal())->toBe('destroyModal');
    expect($events->resetModal())->toBe('resetModal');
    expect($events->all())->toMatchArray([
        'open' => 'openModal',
        'open_sheet' => 'openBottomSheet',
        'close' => 'closeModal',
        'close_top' => 'closeTopModal',
        'close_all' => 'closeAllModals',
        'destroy' => 'destroyModal',
        'reset' => 'resetModal',
    ]);
});

it('reads customized event names through modal facade', function (): void {
    config()->set('corepine-modal.events.listen.open_sheet', ['corepine-modal.custom.open-sheet', 'fallback-sheet']);
    config()->set('corepine-modal.events.listen.close', 'corepine-modal.custom.close');

    expect(ModalFacade::event()->openBottomSheet())->toBe('corepine-modal.custom.open-sheet');
    expect(ModalFacade::event()->closeModal())->toBe('corepine-modal.custom.close');
});

it('supports resolving modal facade class through app helper for blade compatibility', function (): void {
    $service = app(\Corepine\Modal\Facades\Modal::class);

    expect($service)->toBeInstanceOf(ModalService::class);
    expect($service->event()->openBottomSheet())->toBe('openBottomSheet');
});
