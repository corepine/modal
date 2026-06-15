<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modal Events
    |--------------------------------------------------------------------------
    |
    | Event names used by the modal host, helpers, and lifecycle hooks.
    | Rename these if your application needs a different event namespace.
    |
    */
    'events' => [
        'listen' => [
            // Incoming events consumed by the modal host and helpers.
            'open' => 'modal.open',
            'open_sheet' => 'modal.open-sheet',
            'close' => 'modal.close',
            'close_top' => 'modal.close-top',
            'close_all' => 'modal.close-all',
            'destroy' => 'modal.destroy',
            'reset' => 'modal.reset',
            'toggle' => 'modal.toggle',
        ],
        'dispatch' => [
            // Outgoing events emitted by the package after host state changes.
            'opened' => 'modal.opened',
            'closed' => 'modal.closed',
            'changed' => 'modal.changed',
            'all_closed' => 'modal.all-closed',
            'component_closed' => 'modal.component-closed',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Modal Attributes
    |--------------------------------------------------------------------------
    |
    | These values are merged into every modal unless a component or runtime
    | payload overrides them.
    |
    */
    'defaults' => [
        'attributes' => [
            // Close behavior.
            'closeOnEscape' => true,
            'closeAllOnEscape' => false,
            'dispatchCloseEvent' => false,
            'destroyOnClose' => true,
            'dismissible' => true,

            // Visual presentation.
            'blur' => false,
            'type' => 'modal',
            'drawer' => false,
            'sheet' => false,
            'isolate' => false,
            'placement' => 'center',
            'origin' => 'center',
            'size' => 'default',
            'height' => null,
            'maxHeight' => null,
            'dragCloseThreshold' => 0.5,
            'class' => '',

            // Built-in shell content.
            'shell' => true,
            'heading' => null,
            'description' => null,
            'showClose' => null,

            // Footer action defaults.
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
        'default' => 'sm:max-w-xl',
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
