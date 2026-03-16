<?php

return [
    'host_component' => 'corepine-modal',

    'events' => [
        'listen' => [
            'open' => ['openModal', 'corepine-modal.open'],
            'open_sheet' => ['openBottomSheet', 'corepine-modal.open-sheet'],
            'close' => ['closeModal', 'corepine-modal.close'],
            'close_top' => ['closeTopModal', 'corepine-modal.close-top'],
            'close_all' => ['closeAllModals', 'corepine-modal.close-all'],
            'destroy' => ['destroyModal', 'corepine-modal.destroy'],
            'reset' => ['resetModal', 'corepine-modal.reset'],
        ],
        'dispatch' => [
            'opened' => 'modalOpened',
            'closed' => 'modalClosed',
            'changed' => 'activeModalChanged',
            'all_closed' => 'allModalsClosed',
            'component_closed' => 'modalComponentClosed',
        ],
    ],

    'defaults' => [
        'attributes' => [
            'closeOnEscape' => true,
            'closeOnEscapeIsForceful' => false,
            'dispatchCloseEvent' => false,
            'destroyOnClose' => true,
            'closeOnClickAway' => true,
            'blur' => false,
            'type' => 'modal',
            'drawer' => false,
            'sheet' => false,
            'isolate' => false,
            'position' => 'center',
            'size' => 'default',
            'class' => '',
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
