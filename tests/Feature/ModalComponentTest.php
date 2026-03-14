<?php

use Corepine\Modal\Tests\Fixtures\Livewire\ControlModal;
use Corepine\Modal\Tests\Fixtures\Livewire\ExampleModal;
use Livewire\Livewire;

beforeEach(function (): void {
    Livewire::component('test.example-modal', ExampleModal::class);
    Livewire::component('test.control-modal', ControlModal::class);
});

it('dispatches open event from modal component helper', function (): void {
    Livewire::test('test.control-modal')
        ->call('openChild')
        ->assertDispatched('openModal');
});

it('dispatches close event with stacked count when skipping previous modal', function (): void {
    Livewire::test('test.control-modal')
        ->call('closeCurrentAndPrevious')
        ->assertDispatched('closeModal');
});

it('dispatches close-top event explicitly', function (): void {
    Livewire::test('test.control-modal')
        ->call('closeTopTwo')
        ->assertDispatched('closeTopModal');
});

it('dispatches close-all when forcing close', function (): void {
    Livewire::test('test.control-modal')
        ->call('forceCloseEverything')
        ->assertDispatched('closeAllModals');
});
