<?php

return [
    'host_component' => 'corepine-modal',

    'events' => [
        'listen' => [
            'open' => ['corepine-modal.open', 'openModal'],
            'open_sheet' => ['corepine-modal.open-sheet', 'openBottomSheet'],
            'close' => ['corepine-modal.close', 'closeModal'],
            'close_top' => ['corepine-modal.close-top', 'closeTopModal'],
            'close_all' => ['corepine-modal.close-all', 'closeAllModals'],
            'destroy' => ['corepine-modal.destroy', 'destroyModal'],
            'reset' => ['corepine-modal.reset', 'resetModal'],
        ],
        'dispatch' => [
            'opened' => 'corepine-modal.opened',
            'closed' => 'corepine-modal.closed',
            'changed' => 'corepine-modal.changed',
            'all_closed' => 'corepine-modal.all-closed',
            'component_closed' => 'corepine-modal.component-closed',
        ],
    ],

    'defaults' => [
        'attributes' => [
            'closeOnEscape' => true,
            'closeAllOnEscape' => false,
            'dispatchCloseEvent' => false,
            'destroyOnClose' => true,
            'closeOnClickAway' => true,
            'dismissible' => true,
            'blur' => false,
            'type' => 'modal',
            'drawer' => false,
            'sheet' => false,
            'isolate' => false,
            'position' => 'center',
            'size' => 'default',
            'height' => null,
            'class' => '',
            'shell' => true,
            'heading' => null,
            'description' => null,
            'showClose' => true,
            'footerActionsAlignment' => 'end',
            'footerActions' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Modal Sizes
    |--------------------------------------------------------------------------
    |
    | Fully custom size tokens. Values are utility class strings.
    | Example: 'sheet' => 'max-w-[92vw]', 'dialog' => 'max-w-2xl'
    |
    */
    'sizes' => [
        'default' => 'max-w-lg sm:max-w-full',
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
        '6xl' => 'max-w-6xl',
        '7xl' => 'max-w-7xl',
    ],
];
