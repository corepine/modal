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

    protected function dispatchCloseEvents(): array
    {
        return [
            'users-refreshed' => ['user' => 5],
        ];
    }

    protected function dispatchCloseEventsTo(): array
    {
        return [
            'test.example-modal' => [
                'focus-user' => ['user' => 5],
            ],
        ];
    }

    public function closeCurrentWithDispatches(): void
    {
        $this->closeModal(
            dispatch: ['users-saved' => ['user' => 9]],
            dispatchTo: ['test.example-modal' => ['sync-user' => ['user' => 9]]],
        );
    }

    public function closeCurrentWithoutDestroy(): void
    {
        $this->closeModal(destroy: false);
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
