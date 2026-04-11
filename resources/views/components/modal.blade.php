@props([
    'includeStyles' => false,
])

@if ($includeStyles)
    <link rel="stylesheet" href="{{ asset('vendor/corepine-modal/app.css') }}">
@endif

@livewire('corepine-modal')
