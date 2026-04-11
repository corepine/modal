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
        ->assertDispatched('corepine-modal.open');
});

it('dispatches open-bottom-sheet event from modal component helper', function (): void {
    Livewire::test('test.control-modal')
        ->call('openSheetChild')
        ->assertDispatched('corepine-modal.open-sheet');
});

it('dispatches close event with stacked count when skipping previous modal', function (): void {
    Livewire::test('test.control-modal')
        ->call('closeCurrentAndPrevious')
        ->assertDispatched('corepine-modal.close');
});

it('dispatches close-top event explicitly', function (): void {
    Livewire::test('test.control-modal')
        ->call('closeTopTwo')
        ->assertDispatched('corepine-modal.close-top');
});

it('dispatches close-all from the closeAll helper', function (): void {
    Livewire::test('test.control-modal')
        ->call('closeAllModalLayers')
        ->assertDispatched('corepine-modal.close-all');
});
