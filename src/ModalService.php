<?php

namespace Corepine\Modal;

use Corepine\Modal\Support\ModalConfig;
use Corepine\Modal\Support\ModalEvents;

class ModalService
{
    private ?ModalEvents $events = null;

    public function __construct(
        private readonly ModalConfig $config
    ) {
    }

    public function event(): ModalEvents
    {
        return $this->events ??= new ModalEvents($this->config);
    }

    public function config(): ModalConfig
    {
        return $this->config;
    }
}
