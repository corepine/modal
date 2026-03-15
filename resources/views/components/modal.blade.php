@props([
    'includeStyles' => false,
])

@if ($includeStyles)
    <link rel="stylesheet" href="{{ asset('vendor/corepine-modal/app.css') }}">
@endif

@php($modalConfig = app(\Corepine\Modal\Support\ModalConfig::class))
@livewire($modalConfig->hostComponent())
