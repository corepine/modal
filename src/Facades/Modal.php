<?php

namespace Corepine\Modal\Facades;

use Corepine\Modal\ModalService;
use Corepine\Modal\Support\ModalConfig;
use Corepine\Modal\Support\ModalEvents;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ModalEvents event()
 * @method static ModalConfig config()
 *
 * @see ModalService
 */
class Modal extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ModalService::class;
    }
}
