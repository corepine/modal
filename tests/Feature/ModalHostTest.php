<?php

use Corepine\Modal\Livewire\ModalHost;
use Corepine\Modal\Tests\Fixtures\Livewire\AttributeSizedModal;
use Corepine\Modal\Tests\Fixtures\Livewire\ExampleModal;
use Livewire\Livewire;

beforeEach(function (): void {
    Livewire::component('test.example-modal', ExampleModal::class);
    Livewire::component('test.attribute-sized-modal', AttributeSizedModal::class);
});

it('opens and stacks modals', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal')
        ->dispatch('openModal', component: 'test.example-modal', arguments: ['title' => 'Second']);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($stack)->toHaveCount(2);
    expect($test->get('activeModalId'))->toBe($stack[1]);
    expect($modals[$stack[1]]['arguments']['title'])->toBe('Second');
});

it('closes top modal layers', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', arguments: ['title' => 'One'])
        ->dispatch('openModal', component: 'test.example-modal', arguments: ['title' => 'Two'])
        ->dispatch('openModal', component: 'test.example-modal', arguments: ['title' => 'Three']);

    $initialStack = $test->get('stack');

    $test->dispatch('closeTopModal', count: 2, destroy: true);

    $stack = $test->get('stack');

    expect($stack)->toHaveCount(1);
    expect($test->get('activeModalId'))->toBe($stack[0]);
    expect($stack[0])->toBe($initialStack[0]);
});

it('force closes all modals', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal')
        ->dispatch('openModal', component: 'test.example-modal')
        ->dispatch('closeTopModal', force: true);

    expect($test->get('stack'))->toBe([]);
    expect($test->get('modals'))->toBe([]);
    expect($test->get('activeModalId'))->toBeNull();
});

it('opens modal by class path', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: ExampleModal::class);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($stack)->toHaveCount(1);
    expect($modals[$stack[0]]['class'])->toBe(ExampleModal::class);
});

it('uses modalSize from component class', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal');

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['size'])->toBe('md');
});

it('allows runtime size override with raw classes', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', modalAttributes: [
            'size' => 'max-w-[900px] sm:max-w-full',
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['size'])->toBe('max-w-[900px] sm:max-w-full');
});

it('stores runtime class and blur attributes', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', modalAttributes: [
            'class' => 'p-8 border border-zinc-200',
            'blur' => true,
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['class'])->toBe('p-8 border border-zinc-200');
    expect($modals[$stack[0]]['modalAttributes']['blur'])->toBeTrue();
});

it('stores drawer and position attributes', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', modalAttributes: [
            'drawer' => true,
            'position' => 'left',
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['drawer'])->toBeTrue();
    expect($modals[$stack[0]]['modalAttributes']['position'])->toBe('left');
});

it('normalizes invalid drawer position to right', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', modalAttributes: [
            'drawer' => true,
            'position' => 'top',
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['position'])->toBe('right');
});

it('stores non-drawer position for centered modal layout overrides', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', modalAttributes: [
            'position' => 'top',
        ]);

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['drawer'])->toBeFalse();
    expect($modals[$stack[0]]['modalAttributes']['position'])->toBe('top');
});

it('forces drawer edge side to remain square in rendered classes', function (): void {
    Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.example-modal', modalAttributes: [
            'drawer' => true,
            'position' => 'right',
            'class' => 'rounded-3xl',
        ])
        ->assertSee('rounded-3xl')
        ->assertSee('rounded-r-none');
});

it('keeps modalAttributes size when defined explicitly', function (): void {
    $test = Livewire::test(ModalHost::class)
        ->dispatch('openModal', component: 'test.attribute-sized-modal');

    $stack = $test->get('stack');
    $modals = $test->get('modals');

    expect($modals[$stack[0]]['modalAttributes']['size'])->toBe('4xl');
});
