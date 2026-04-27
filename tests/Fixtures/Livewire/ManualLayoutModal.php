<?php

namespace Corepine\Modal\Tests\Fixtures\Livewire;

use Corepine\Modal\Modal;

class ManualLayoutModal extends Modal
{
    public function render()
    {
        return view('livewire.manual-layout-modal');
    }
}
