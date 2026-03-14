<?php

namespace Corepine\Modal\Tests\Fixtures\Livewire;

use Corepine\Modal\Modal;

class ControlModal extends Modal
{
    public function openChild(): void
    {
        $this->openModal('test.example-modal', ['title' => 'Child']);
    }

    public function closeCurrentAndPrevious(): void
    {
        $this->skipPreviousModal(1)->closeModal();
    }

    public function closeTopTwo(): void
    {
        $this->closeTopModal(2);
    }

    public function forceCloseEverything(): void
    {
        $this->forceClose()->closeModal();
    }

    public function render()
    {
        return <<<'HTML'
        <div>Control</div>
        HTML;
    }
}
