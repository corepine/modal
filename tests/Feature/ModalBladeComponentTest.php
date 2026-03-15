<?php

use Illuminate\Support\Facades\Blade;

it('renders modal shell with header, body, and footer slots', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal title="Edit User">
    <div>Modal body content</div>
    <x-slot:footer>
        <button type="button">Save</button>
    </x-slot:footer>
</x-corepine-modal>
BLADE);

    expect($html)->toContain('cp-modal-layout');
    expect($html)->toContain('cp-modal-header');
    expect($html)->toContain('cp-modal-body');
    expect($html)->toContain('cp-modal-footer');
    expect($html)->toContain('max-h-[90vh]');
    expect($html)->toContain('h-16');
    expect($html)->not->toContain('mt-auto');
    expect($html)->toContain('Edit User');
    expect($html)->toContain('Modal body content');
    expect($html)->toContain('Save');
});

it('supports null title while keeping close action by default', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal :title="null">
    <div>Body only</div>
</x-corepine-modal>
BLADE);

    expect($html)->toContain('Body only');
    expect($html)->toContain('sr-only');
    expect($html)->not->toContain('cp-modal-title');
    expect($html)->toContain('max-h-[96vh]');
    expect($html)->not->toContain('cp-modal-footer');
});

it('merges custom wrapper attributes on modal shell', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-corepine-modal title="Custom" id="users-modal" class="ring-1 ring-zinc-300">
    <div>Body</div>
</x-corepine-modal>
BLADE);

    expect($html)->toContain('id="users-modal"');
    expect($html)->toContain('ring-1');
    expect($html)->toContain('cp-modal-layout');
});
