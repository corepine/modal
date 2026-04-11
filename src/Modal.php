<?php

namespace Corepine\Modal;

use Corepine\Modal\Support\ModalConfig;
use Livewire\Component;

abstract class Modal extends Component
{
    protected int $closeLayers = 1;

    protected bool $destroySkipped = true;

    public function destroySkippedModals(bool $destroy = true): self
    {
        $this->destroySkipped = $destroy;

        return $this;
    }

    public function skipPreviousModals(int $count = 1, bool $destroy = true): self
    {
        return $this->skipPreviousModal($count, $destroy);
    }

    public function skipPreviousModal(int $count = 1, bool $destroy = true): self
    {
        $this->closeLayers = max(1, $count + 1);
        $this->destroySkipped = $destroy;

        return $this;
    }

    public function closeAll(bool $destroy = true): void
    {
        $this->closeAllModals($destroy);
        $this->resetCloseState();
    }

    public function openModal(string $component, array $arguments = [], array $modalAttributes = []): void
    {
        $this->dispatch(
            $this->modalConfig()->listenEvent('open'),
            component: $component,
            arguments: $arguments,
            modalAttributes: $modalAttributes
        );
    }

    public function openBottomSheet(string $component, array $arguments = [], array $modalAttributes = []): void
    {
        $this->dispatch(
            $this->modalConfig()->listenEvent('open_sheet'),
            component: $component,
            arguments: $arguments,
            modalAttributes: array_replace($modalAttributes, ['type' => 'sheet', 'sheet' => true])
        );
    }

    public function closeTopModal(int $count = 1, bool $destroy = true): void
    {
        $this->dispatch(
            $this->modalConfig()->listenEvent('close_top'),
            count: max(1, $count),
            destroy: $destroy
        );
    }

    public function closeAllModals(bool $destroy = true): void
    {
        $this->dispatch(
            $this->modalConfig()->listenEvent('close_all'),
            destroy: $destroy
        );
    }

    public function closeModal(): void
    {
        $this->dispatch(
            $this->modalConfig()->listenEvent('close'),
            count: max(1, $this->closeLayers),
            destroy: $this->destroySkipped
        );
        $this->resetCloseState();
    }

    public function closeModalWithEvents(array $events): void
    {
        $this->dispatchModalEvents($events);
        $this->closeModal();
    }

    public static function modalSize(): string
    {
        $defaults = app(ModalConfig::class)->defaultModalAttributes();
        $size = $defaults['size'] ?? 'default';

        return is_string($size) && $size !== '' ? $size : 'default';
    }

    public static function modalAttributes(): array
    {
        $attributes = app(ModalConfig::class)->defaultModalAttributes();
        $attributes['size'] = static::modalSize();

        return $attributes;
    }

    protected function resetCloseState(): void
    {
        $this->closeLayers = 1;
        $this->destroySkipped = true;
    }

    protected function modalConfig(): ModalConfig
    {
        return app(ModalConfig::class);
    }

    private function dispatchModalEvents(array $events): void
    {
        foreach ($events as $component => $event) {
            $params = [];

            if (is_array($event)) {
                [$event, $params] = $event;
            }

            if (is_numeric($component)) {
                $this->dispatch($event, ...$params);

                continue;
            }

            $this->dispatch($event, ...$params)->to($component);
        }
    }
}
