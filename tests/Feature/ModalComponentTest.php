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
        ->assertDispatched('modal.open');
});

it('dispatches open-bottom-sheet event from modal component helper', function (): void {
    Livewire::test('test.control-modal')
        ->call('openSheetChild')
        ->assertDispatched('modal.open-sheet');
});

it('dispatches close event with stacked count when skipping previous modal', function (): void {
    Livewire::test('test.control-modal')
        ->call('closeCurrentAndPrevious')
        ->assertDispatched('modal.close');
});

it('merges explicit and component-defined post-close dispatch payloads', function (): void {
    Livewire::test('test.control-modal')
        ->call('closeCurrentWithDispatches')
        ->assertDispatched('modal.close', function (string $name, array $params): bool {
            return ($params['dispatch'] ?? []) === [
                'users-refreshed' => ['user' => 5],
                'users-saved' => ['user' => 9],
            ] && ($params['dispatchTo'] ?? []) === [
                'test.example-modal' => [
                    'focus-user' => ['user' => 5],
                    'sync-user' => ['user' => 9],
                ],
            ];
        });
});

it('supports overriding destroy with named closeModal arguments', function (): void {
    Livewire::test('test.control-modal')
        ->call('closeCurrentWithoutDestroy')
        ->assertDispatched('modal.close', destroy: false);
});

it('dispatches close-top event explicitly', function (): void {
    Livewire::test('test.control-modal')
        ->call('closeTopTwo')
        ->assertDispatched('modal.close-top');
});

it('dispatches close-all from the closeAll helper', function (): void {
    Livewire::test('test.control-modal')
        ->call('closeAllModalLayers')
        ->assertDispatched('modal.close-all');
});
