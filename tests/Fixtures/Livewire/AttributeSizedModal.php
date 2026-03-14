<?php

namespace Corepine\Modal\Tests\Fixtures\Livewire;

use Corepine\Modal\Modal;

class AttributeSizedModal extends Modal
{
    public static function modalSize(): string
    {
        return 'md';
    }

    public static function modalAttributes(): array
    {
        return [
            'size' => '4xl',
        ];
    }

    public function render()
    {
        return <<<'HTML'
        <div>Attribute sized modal</div>
        HTML;
    }
}
