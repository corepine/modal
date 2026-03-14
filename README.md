# Corepine Modal

Corepine Modal is a stack-based modal package for **Livewire v3/v4** with **Laravel 11/12/13** support.

It is built around:
- `dispatch` events (no `$emit`)
- reusable modal classes (`extends Modal`)
- child modal stacking
- safe model argument resolution from IDs

## Requirements

- PHP `^8.2|^8.3|^8.4`
- Laravel `^11.0|^12.0|^13.0`
- Livewire `^3.7|^4.0`

## Installation

```bash
composer require corepine/modal
```

## Add The Host

Add once in your main layout:

```blade
<x-corepine-modal />
```

or:

```blade
@corepineModal
```

## Tailwind v3 Setup

Import package CSS in your app `app.css`:

```css
@import "../../vendor/corepine/modal/resources/css/app.css";
```

Make sure Tailwind scans package views (or published views):

```js
export default {
  content: [
    './resources/views/**/*.blade.php',
    './vendor/corepine/modal/resources/views/**/*.blade.php',
    './storage/framework/views/*.php',
  ],
  safelist: [
    {
      pattern: /max-w-(sm|md|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl)/,
      variants: ['sm', 'md', 'lg', 'xl', '2xl']
    }
  ]
}
```

## Create A Modal

Use `Corepine\Modal\Modal` as the base class:

```php
<?php

namespace App\Livewire\Modals;

use App\Models\User;
use Corepine\Modal\Modal;

class EditUser extends Modal
{
    public User $user;

    public static function modalAttributes(): array
    {
        return [
            'width' => 'xl',
            'closeOnEscape' => true,
            'closeOnClickAway' => true,
            'closeOnEscapeIsForceful' => false,
            'destroyOnClose' => true,
            'dispatchCloseEvent' => true,
            'panelClass' => 'p-6',
        ];
    }

    public function save(): void
    {
        // ...
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.modals.edit-user');
    }
}
```

## Open Modals (dispatch-first)

### Outside Livewire (JS)

```html
<button onclick="Livewire.dispatch('openModal', { component: 'modals.edit-user', arguments: { user: 5 } })">
    Edit
</button>
```

### Inside Livewire Blade

```blade
<button wire:click="$dispatch('openModal', { component: 'modals.edit-user', arguments: { user: {{ $user->id }} } })">
    Edit
</button>
```

### Open By Class Path

```blade
<button wire:click="$dispatch('openModal', { component: '{{ \App\Livewire\Modals\EditUser::class }}', arguments: { user: {{ $user->id }} } })">
    Edit
</button>
```

### Blade Helper Component

```blade
<x-corepine-open-modal
    :component-class="\App\Livewire\Modals\EditUser::class"
    :arguments="['user' => $user->id]"
>
    <button type="button">Edit</button>
</x-corepine-open-modal>
```

## Closing Modals

From Blade:

```blade
<button wire:click="$dispatch('closeModal')">Close</button>
<button wire:click="$dispatch('closeTopModal', { count: 2 })">Close 2</button>
<button wire:click="$dispatch('closeAllModals')">Close All</button>
```

From modal class:

```php
// Close current modal
$this->closeModal();

// Close current + previous modal
$this->skipPreviousModal()->closeModal();

// Close current + N previous
$this->skipPreviousModals(2)->closeModal();

// Force close everything
$this->forceClose()->closeModal();

// Close and dispatch to other components
$this->closeModalWithEvents([
    \App\Livewire\Users\Table::class => ['usersRefreshed', [$this->user->id]],
]);
```

## Safe Model Argument Resolution

If your modal has typed public properties (for example `public User $user`), passing `user: 5` will resolve the model via route binding before the component mounts.

Enums are also resolved via `tryFrom` when type-hinted.

## Prevent Close On Escape / Click Away

The active modal receives these events:
- `closingModalOnEscape`
- `closingModalOnClickAway`

Inside modal Blade you can cancel closing:

```blade
@script
<script>
    $wire.on('closingModalOnEscape', (payload) => {
        if ($wire.isDirty) payload.closing = false;
    });
</script>
@endscript
```

## Config

Publish config:

```bash
php artisan vendor:publish --tag=corepine-modal-config
```

Config file: `config/corepine-modal.php`

You can customize:
- host component name
- incoming/outgoing event names
- modal defaults
- width class mapping

## Config Service

Use `Corepine\Modal\Support\ModalConfig` if you need consistent package values in your own classes:

```php
$modalConfig = app(\Corepine\Modal\Support\ModalConfig::class);
$openEvent = $modalConfig->listenEvent('open'); // openModal
$closedEvent = $modalConfig->dispatchEvent('closed'); // modalClosed
```

## Testing

```bash
composer test
```

Package tests are written with Pest.
