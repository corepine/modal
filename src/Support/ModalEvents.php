<?php

namespace Corepine\Modal\Support;

class ModalEvents
{
    public function __construct(
        private readonly ModalConfig $config
    ) {
    }

    public function open(): string
    {
        return $this->config->listenEvent('open');
    }

    public function openModal(): string
    {
        return $this->open();
    }

    public function openBottomSheet(): string
    {
        return $this->config->listenEvent('open_sheet');
    }

    public function close(): string
    {
        return $this->config->listenEvent('close');
    }

    public function closeModal(): string
    {
        return $this->close();
    }

    public function closeTop(): string
    {
        return $this->config->listenEvent('close_top');
    }

    public function closeTopModal(): string
    {
        return $this->closeTop();
    }

    public function closeAll(): string
    {
        return $this->config->listenEvent('close_all');
    }

    public function closeAllModals(): string
    {
        return $this->closeAll();
    }

    public function destroy(): string
    {
        return $this->config->listenEvent('destroy');
    }

    public function destroyModal(): string
    {
        return $this->destroy();
    }

    public function reset(): string
    {
        return $this->config->listenEvent('reset');
    }

    public function resetModal(): string
    {
        return $this->reset();
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return [
            'open' => $this->open(),
            'open_sheet' => $this->openBottomSheet(),
            'close' => $this->close(),
            'close_top' => $this->closeTop(),
            'close_all' => $this->closeAll(),
            'destroy' => $this->destroy(),
            'reset' => $this->reset(),
        ];
    }
}
