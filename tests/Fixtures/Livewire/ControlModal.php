<?php

namespace Corepine\Modal\Tests\Fixtures\Livewire;

use Corepine\Modal\Modal;

class ControlModal extends Modal
{
    public function openChild(): void
    {
        $this->openModal('test.example-modal', ['title' => 'Child']);
    }

    public function openSheetChild(): void
    {
        $this->openBottomSheet('test.example-modal', ['title' => 'Sheet Child']);
    }

    public function closeCurrentAndPrevious(): void
    {
        $this->skipPreviousModal(1)->closeModal();
    }

    public function closeTopTwo(): void
    {
        $this->closeTopModal(2);
    }

    public function closeAllModalLayers(): void
    {
        $this->closeAll();
    }

    public function render()
    {
        return <<<'HTML'
        <div>Control</div>
        HTML;
    }
}
