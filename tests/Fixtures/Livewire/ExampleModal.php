<?php

namespace Corepine\Modal\Tests\Fixtures\Livewire;

use Corepine\Modal\Modal;

class ExampleModal extends Modal
{
    public string $title = 'Example modal';

    public static function modalSize(): string
    {
        return 'md';
    }

    public static function modalAttributes(): array
    {
        return [
            'closeOnEscape' => true,
            'closeOnEscapeIsForceful' => false,
            'dispatchCloseEvent' => false,
            'destroyOnClose' => true,
            'closeOnClickAway' => true,
            'modalClass' => 'p-4',
        ];
    }

    public function render()
    {
        return view('livewire.example-modal');
    }
}
