<?php

return [
    'events' => [
        'dispatch' => [
            // Outgoing events emitted by the package after host state changes.
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
            'dismissible' => true,
            'blur' => false,
            'type' => 'modal',
            'drawer' => false,
            'sheet' => false,
            'isolate' => false,
            'position' => 'center',
            'origin' => 'center',
            'size' => 'default',
            'height' => null,
            'maxHeight' => null,
            'class' => '',
            'shell' => true,
            'heading' => null,
            'description' => null,
            'showClose' => true,
            'footerActionsAlignment' => 'end',
            'actions' => [],
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
