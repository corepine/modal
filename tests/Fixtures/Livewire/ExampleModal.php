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
            'closeAllOnEscape' => false,
            'dispatchCloseEvent' => false,
            'destroyOnClose' => true,
            'dismissible' => true,
            'class' => 'p-4',
        ];
    }

    public function render()
    {
        return view('livewire.example-modal');
    }
}
