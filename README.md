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

`<x-corepine-modal />` (without slot content) renders the global modal host.

## Modal Shell Component

Use `<x-corepine-modal-layout>` as the reusable shell inside your modal views so you do not repeat header, close button, and footer layout.

Alias: `<x-corepine-modal-template>`.

Props:
- `title` (nullable)
- `showClose` (default: `true`)

Named slot:
- `footer`

Example:

```blade
<x-corepine-modal-layout title="Manage Users">
    <div class="space-y-3">
        <!-- main content -->
    </div>

    <x-slot:footer>
        <div class="flex justify-end gap-2">
            <x-corepine-modal-close class="rounded-md border px-3 py-2 text-sm">Cancel</x-corepine-modal-close>
            <button type="submit" class="rounded-md bg-zinc-900 px-3 py-2 text-sm text-white">Save</button>
        </div>
    </x-slot:footer>
</x-corepine-modal-layout>
```

Notes:
- Header and footer are separated from body.
- Body is the dedicated scroll area (`minmax(0,1fr)`), so footer stays visible and does not get overlapped by long content.
- Title can be `null`; close button still appears by default.

## Tailwind v4 Setup

Import package CSS in your app `app.css`:

```css
@import "../../vendor/corepine/modal/resources/css/app.css";
```

No `tailwind.config.js` is required for this package setup.

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

    public static function modalSize(): string
    {
        return 'xl'; // token from config('corepine-modal.sizes')
    }

    public static function modalAttributes(): array
    {
        return [
            'closeOnEscape' => true,
            'closeOnClickAway' => true,
            'blur' => true,
            'drawer' => false,
            'position' => 'center',
            'closeOnEscapeIsForceful' => false,
            'destroyOnClose' => true,
            'dispatchCloseEvent' => true,
            'class' => 'p-6',
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

If you prefer, you can set size directly in `modalAttributes()`:

```php
public static function modalAttributes(): array
{
    return [
        'size' => '3xl',
        // or raw classes:
        // 'size' => 'max-w-[960px] sm:max-w-full',
        'blur' => true,
        'drawer' => true,
        'position' => 'right',
        'class' => 'p-6',
    ];
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
<x-corepine-modal-open
    :component-class="\App\Livewire\Modals\EditUser::class"
    :arguments="['user' => $user->id]"
    class="p-8 bg-white border border-zinc-200 rounded-3xl"
    size="2xl"
    blur="true"
    position="right"
    drawer="true"
    isolate="true"
>
    <button type="button">Edit</button>
</x-corepine-modal-open>
```

You can also pass raw classes instead of a size token:

```blade
<x-corepine-modal-open
    component="modals.edit-user"
    :arguments="['user' => $user->id]"
    size="max-w-[900px] sm:max-w-full"
/>
```

`class` is the styling hook for modal surface styling (background, border, rounded, padding, etc.).
When used on `<x-corepine-modal-open ...>`, `class` is forwarded to the modal component (not the trigger wrapper).

## Drawer And Position

- `position` works for standard modals: `center`, `top`, `bottom`, `left`, `right`.
- `drawer=true` enables drawer mode with horizontal slide transitions.
- Drawer mode only accepts `left` or `right`; invalid values fallback to `right`.
- Drawer panels are `h-full` by default.
- Drawer width is controlled by `size` (token or raw classes), same as regular modals.
- Drawer edge behavior is enforced by default:
  - `position="left"` => left edge is not rounded
  - `position="right"` => right edge is not rounded

Examples:

```blade
<x-corepine-modal-open
    component="modals.filters"
    drawer="true"
    position="left"
    size="max-w-[420px]"
    class="h-full rounded-none border-r border-zinc-200"
/>
```

```blade
<x-corepine-modal-open
    component="modals.profile"
    drawer="true"
    position="right"
    size="sheet"
/>
```

```php
public static function modalAttributes(): array
{
    return [
        'drawer' => true,
        'position' => 'right',
        'size' => 'sheet',
    ];
}
```

## Isolate Stacking

- `isolate=true` on the active modal keeps previous stacked modal layers visible.
- Non-active layers remain non-interactive while isolated modal is on top.
- `isolate=false` (default) keeps classic behavior where only the active modal layer is shown.

From Blade helper:

```blade
<x-corepine-modal-open component="modals.edit-user" isolate="true" />
```

From modal class:

```php
public static function modalAttributes(): array
{
    return [
        'isolate' => true,
    ];
}
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
- size tokens
- default blur
- default drawer + position behavior

Example:

```php
'sizes' => [
    'default' => 'max-w-lg sm:max-w-full',
    'sheet' => 'max-w-[92vw]',
    'dialog' => 'max-w-2xl',
    'editor' => 'max-w-5xl',
],
```

How this works:
- left side (`default`, `sheet`, `dialog`, `editor`) is the size name/token
- right side is the Tailwind width classes applied to the modal component

So:
- `size="dialog"` applies `max-w-2xl`
- `size="editor"` applies `max-w-5xl`
- if no size is provided, `default` is used

For drawers:
- `size` still controls width
- height is full by default (`h-full`)
- position controls side (`left` or `right`)

Use token from Blade:

```blade
<x-corepine-modal-open size="editor" ... />
```

Use token from modal class:

```php
public static function modalSize(): string
{
    return 'editor';
}
```

You can also bypass the token map and pass raw classes directly:

```blade
<x-corepine-modal-open size="max-w-[900px] sm:max-w-full" ... />
```

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
