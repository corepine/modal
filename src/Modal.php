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

    public function skipPreviousModals(int $layers = 1, bool $destroy = true): self
    {
        return $this->skipPreviousModal($layers, $destroy);
    }

    public function skipPreviousModal(int $layers = 1, bool $destroy = true): self
    {
        $this->closeLayers = max(1, $layers + 1);
        $this->destroySkipped = $destroy;

        return $this;
    }

    public function closeAll(bool $destroy = true, array $dispatch = [], array $dispatchTo = []): void
    {
        $this->closeAllModals($destroy, $dispatch, $dispatchTo);
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

    public function closeTopModal(int $layers = 1, bool $destroy = true, array $dispatch = [], array $dispatchTo = []): void
    {
        [$dispatch, $dispatchTo] = $this->resolvedCloseDispatches($dispatch, $dispatchTo);

        $this->dispatch(
            $this->modalConfig()->listenEvent('close_top'),
            layers: max(1, $layers),
            destroy: $destroy,
            dispatch: $dispatch,
            dispatchTo: $dispatchTo,
        );
    }

    public function closeAllModals(bool $destroy = true, array $dispatch = [], array $dispatchTo = []): void
    {
        [$dispatch, $dispatchTo] = $this->resolvedCloseDispatches($dispatch, $dispatchTo);

        $this->dispatch(
            $this->modalConfig()->listenEvent('close_all'),
            destroy: $destroy,
            dispatch: $dispatch,
            dispatchTo: $dispatchTo,
        );
    }

    public function closeModal(?bool $destroy = null, array $dispatch = [], array $dispatchTo = []): void
    {
        [$dispatch, $dispatchTo] = $this->resolvedCloseDispatches($dispatch, $dispatchTo);
        $destroy ??= $this->destroySkipped;

        $this->dispatch(
            $this->modalConfig()->listenEvent('close'),
            layers: max(1, $this->closeLayers),
            destroy: $destroy,
            dispatch: $dispatch,
            dispatchTo: $dispatchTo,
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

    protected function dispatchCloseEvents(): array
    {
        return [];
    }

    protected function dispatchCloseEventsTo(): array
    {
        return [];
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

    /**
     * @return array{0: array<string, mixed>, 1: array<string, array<string, mixed>>}
     */
    private function resolvedCloseDispatches(array $dispatch, array $dispatchTo): array
    {
        $mergedDispatch = array_replace($this->dispatchCloseEvents(), $dispatch);
        $mergedDispatchTo = $this->dispatchCloseEventsTo();

        foreach ($dispatchTo as $component => $events) {
            if (! is_string($component) || trim($component) === '' || ! is_array($events)) {
                continue;
            }

            $component = trim($component);
            $mergedDispatchTo[$component] = array_replace(
                is_array($mergedDispatchTo[$component] ?? null) ? $mergedDispatchTo[$component] : [],
                $events
            );
        }

        return [$mergedDispatch, $mergedDispatchTo];
    }
}
