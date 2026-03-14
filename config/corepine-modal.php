<?php

return [
    'host_component' => 'corepine-modal',

    'events' => [
        'listen' => [
            'open' => ['openModal', 'corepine-modal.open'],
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
            'width' => '2xl',
            'panelClass' => '',
        ],
    ],

    'width_classes' => [
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
