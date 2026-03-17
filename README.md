# Corepine Modal

Corepine Modal is a stack-based modal package for **Livewire v3/v4** with **Laravel 11/12/13** support.

It is built around:
- dispatch events (no `$emit`)
- reusable modal classes (`extends Corepine\Modal\Modal`)
- stacked modals (open child on top of parent)
- safe model/enum argument resolution from IDs
- multiple presentation types: `modal`, `drawer`, `sheet`
- mobile-friendly sheet interactions (drag down to close + resize handle)

## Requirements

- PHP `^8.2|^8.3|^8.4`
- Laravel `^11.0|^12.0|^13.0`
- Livewire `^3.7|^4.0`

## Installation

```bash
composer require corepine/modal
```

## Render The Global Host

Add once in your app layout:

```blade
<x-corepine.modal.assets />
```

Or with directive:

```blade
@corepineModal
```

Notes:
- This host must exist for open/close dispatch events to work.
- `<x-corepine.modal.assets include-styles />` can include the published CSS file (`public/vendor/corepine-modal/app.css`) if you need it.
- Legacy host alias `<x-corepine-modal />` still works.

## Tailwind v4 Setup

Import package CSS into your main stylesheet:

```css
@import "../../vendor/corepine/modal/resources/css/app.css";
```

No `tailwind.config.js` is required for this package itself.

## Blade Components

Preferred dotted aliases:
- `<x-corepine.modal.assets />`: renders the global Livewire modal host.
- `<x-corepine.modal.layout />`: reusable shell (header/body/footer) for modal content.
- `<x-corepine.modal.template />`: alias of `x-corepine.modal.layout`.
- `<x-corepine.modal.actions.open />`: trigger helper (dispatches open event).
- `<x-corepine.modal.actions.close />`: close helper (dispatches close event).

Legacy aliases still supported:
- `<x-corepine-modal />`, `<x-corepine-modal-layout />`, `<x-corepine-modal-template />`
- `<x-corepine-modal-actions-open />`, `<x-corepine-modal-actions-close />`
- `<x-corepine-modal-open />`, `<x-corepine-modal-close />`
- `<x-corepine-open-modal />`, `<x-corepine-close-modal />`

## Create A Modal Class

```php
<?php

namespace App\Livewire\Modals;

use App\Models\User;
use Corepine\Modal\Enums\ModalType;
use Corepine\Modal\Modal;

class EditUser extends Modal
{
    public User $user;

    public static function modalSize(): string
    {
        return 'xl';
    }

    public static function modalAttributes(): array
    {
        return [
            'type' => ModalType::Modal,
            'position' => 'center',
            'closeOnEscape' => true,
            'closeOnClickAway' => true,
            'closeOnEscapeIsForceful' => false,
            'destroyOnClose' => true,
            'dispatchCloseEvent' => false,
            'blur' => false,
            'class' => 'p-6',
        ];
    }
}
```

## Layout Shell (`x-corepine.modal.layout`)

Use this inside your modal view so you do not repeat title/close/footer structure.

Props:
- `title` (nullable)
- `description` (nullable)
- `showClose` (`true` by default)

Named slot:
- `footer`

Example:

```blade
<x-corepine.modal.layout title="Manage Users" description="Search and edit users">
    <div class="space-y-3">
        <!-- body content -->
    </div>

    <x-slot:footer>
        <div class="flex justify-end gap-2">
            <x-corepine.modal.actions.close class="rounded-md border px-3 py-2 text-sm">Cancel</x-corepine.modal.actions.close>
            <button type="submit" class="rounded-md bg-zinc-900 px-3 py-2 text-sm text-white">Save</button>
        </div>
    </x-slot:footer>
</x-corepine.modal.layout>
```

## Open Modals

### Outside Livewire (JavaScript)

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

### Open Helper Component

```blade
<x-corepine.modal.actions.open
    :component-class="\App\Livewire\Modals\EditUser::class"
    :arguments="['user' => $user->id]"
    type="drawer"
    position="right"
    size="2xl"
    isolate="true"
    blur="true"
    class="bg-white p-6 rounded-none"
>
    <button type="button">Edit</button>
</x-corepine.modal.actions.open>
```

Open helper props:
- `component`, `componentClass`
- `arguments`
- `modalAttributes` (raw modal attributes array)
- `size`, `height`, `blur`, `type`, `drawer`, `sheet`, `isolate`, `isolated`, `position`
- `class` (forwarded to modal surface class, not trigger wrapper class)

## Modal Service + Facade

The package exposes a config-aware service and facade so event names are never hardcoded.
The facade alias `Modal` is auto-registered via Laravel package discovery.

PHP usage:

```php
use Corepine\Modal\Facades\Modal;

$openEvent = Modal::event()->openModal();
$openSheetEvent = Modal::event()->openBottomSheet();
$closeEvent = Modal::event()->closeModal();
```

Blade + JS usage:

```blade
<button
    onclick="Livewire.dispatch(@js(\Corepine\Modal\Facades\Modal::event()->openBottomSheet()), {
        component: 'users.view',
        arguments: { user: {{ $listedUser->id }} }
    })"
>
    View user
</button>
```

## Close Modals

From modal class:

```php
// close current modal
$this->closeModal();

// close current + 1 previous modal
$this->skipPreviousModal()->closeModal();

// close current + N previous modals
$this->skipPreviousModals(2)->closeModal();

// force close all
$this->forceClose()->closeModal();

// close + dispatch other component events
$this->closeModalWithEvents([
    \App\Livewire\Users\Table::class => ['usersRefreshed', [$this->user->id]],
]);
```

From Blade:

```blade
<button wire:click="$dispatch('closeModal')">Close</button>
<button wire:click="$dispatch('closeTopModal', { count: 2 })">Close 2</button>
<button wire:click="$dispatch('closeAllModals')">Close All</button>
```

Close helper:

```blade
<x-corepine.modal.actions.close count="1" destroy="true" force="false">
    Close
</x-corepine.modal.actions.close>
```

Close helper props:
- `count` (minimum 1)
- `destroy` (default `true`)
- `force` (default `false`, closes all)

## Presentation Types

Supported types:
- `modal`
- `drawer`
- `sheet`

Type can be passed as:
- string (`'sheet'`, `'drawer'`, `'modal'`)
- enum (`ModalType::Sheet`, etc.)

Legacy aliases still supported:
- `drawer=true` resolves type to `drawer`
- `sheet=true` resolves type to `sheet`

## Position Rules

- `type=modal`: `center`, `top`, `bottom`, `left`, `right` (default `center`)
- `type=drawer`: `left` or `right` only (default `right`)
- `type=sheet`: `bottom` only (default `bottom`)

Invalid positions are normalized to the safe default for that type.

## Stack Isolation

Use `isolate=true` when you want previous modal layers to remain visible while a new modal is active.

Example:

```blade
<x-corepine-modal-open
    component="modals.delete-user"
    :arguments="['user' => $user->id]"
    isolate="true"
>
    <button type="button">Delete</button>
</x-corepine-modal-open>
```

Behavior:
- Active modal remains the only interactive layer.
- Previous layers can stay visible for context.
- Works with `modal`, `drawer`, and `sheet`.

## Size System

`size` can be:
- a token from `config('corepine-modal.sizes')` (for example `2xl`)
- raw utility classes (for example `max-w-[900px] sm:max-w-full`)

Defaults include:
- `default`, `sm`, `md`, `lg`, `xl`, `2xl`, `3xl`, `4xl`, `5xl`, `6xl`, `7xl`

## Sheet Interaction (Drag + Resize)

For `type=sheet`:
- Top handle can resize sheet height.
- You can drag the sheet downward to close.
- Drag-close threshold defaults to `30%` of current sheet height.
- Sheet closes through normal close events, so stack behavior remains consistent.

Height defaults by type:
- `modal`: `50vh`
- `drawer`: `100vh`
- `sheet`: `70vh`

Use `height` for all three types.

Recommended sheet config:

```php
public static function modalAttributes(): array
{
    return [
        'type' => 'sheet',
        'draggable' => true,
        'height' => '70vh',
        'sheetMinHeight' => '40vh',
        'sheetMaxHeight' => '95vh',
        'dragCloseThreshold' => 0.3,
    ];
}
```

Height value formats supported:
- integer/float `0..1` as viewport ratio (`0.7` = 70% viewport height)
- integer/float `1..100` as viewport percent (`70` = 70% viewport height)
- integer/float `>100` as pixels (`520`)
- strings with `px`, `vh`, `dvh`, `%`
- `'full'`

## Complete Modal Attributes Reference

The table below covers all supported modal attributes (including legacy aliases and sheet-specific runtime options).

| Attribute | Type | Default | Applies To | Notes |
| --- | --- | --- | --- | --- |
| `closeOnEscape` | bool | `true` | all | Escape key closes active modal when true. |
| `closeOnClickAway` | bool | `true` | all | Backdrop click closes active modal when true. |
| `closeOnEscapeIsForceful` | bool | `false` | all | Escape closes full stack when true. |
| `destroyOnClose` | bool | `true` | all | Destroy component state when modal closes. |
| `dispatchCloseEvent` | bool | `false` | all | Dispatches `modalComponentClosed` when closing this component. |
| `blur` | bool | `false` | all | Adds blurred backdrop style. |
| `type` | string/enum | `modal` | all | `modal`, `drawer`, `sheet`. |
| `drawer` | bool | `false` | all | Legacy alias for `type=drawer`. |
| `sheet` | bool | `false` | all | Legacy alias for `type=sheet`. |
| `isolate` | bool | `false` | all | Keeps previous layers visible behind active modal. |
| `isolated` | bool | n/a | all | Legacy alias of `isolate`. |
| `position` | string | type-based | all | Normalized per type rules. |
| `size` | string | `default` | all | Token from config sizes or raw width classes. |
| `height` | string/number | type-based | all | Shared height attribute for modal/drawer/sheet (`50vh`, `100vh`, `70vh`). |
| `class` | string | `''` | all | Extra classes merged on modal surface (`cp-modal-component`). |
| `draggable` | bool | `true` for sheet | sheet | Enables sheet drag/resize behavior. |
| `dragCloseThreshold` | float | `0.3` | sheet | Close when drag-down reaches threshold ratio. |
| `sheetDragThreshold` | float | alias | sheet | Alias of `dragCloseThreshold`. |
| `sheetHeight` | string/number | alias | sheet | Legacy alias for `height` on sheets. |
| `sheetMinHeight` | string/number | `260px` runtime default | sheet | Minimum sheet height. |
| `minHeight` | string/number | alias | sheet | Alias for `sheetMinHeight`. |
| `sheetMaxHeight` | string/number | `calc(100dvh - 16px)` runtime default | sheet | Maximum sheet height. |
| `maxHeight` | string/number | alias | sheet | Alias for `sheetMaxHeight`. |

Attribute merge order:
1. `config('corepine-modal.defaults.attributes')`
2. modal component `modalAttributes()`
3. runtime open payload (`openModal`, helper `modalAttributes`, etc.)

## Events Reference

Default listen events:
- `openModal`, `corepine-modal.open`
- `openBottomSheet`, `corepine-modal.open-sheet`
- `closeModal`, `corepine-modal.close`
- `closeTopModal`, `corepine-modal.close-top`
- `closeAllModals`, `corepine-modal.close-all`
- `destroyModal`, `corepine-modal.destroy`
- `resetModal`, `corepine-modal.reset`

Default dispatch events:
- `modalOpened`
- `modalClosed`
- `activeModalChanged`
- `allModalsClosed`
- `modalComponentClosed`

All names are customizable in config.

## Config

Publish config:

```bash
php artisan vendor:publish --tag=corepine-modal-config
```

Config file:
- `config/corepine-modal.php`

Main config areas:
- `host_component`
- `events.listen`
- `events.dispatch`
- `defaults.attributes`
- `sizes`

Example custom sizes:

```php
'sizes' => [
    'default' => 'max-w-lg sm:max-w-full',
    'sheet' => 'max-w-[92vw]',
    'dialog' => 'max-w-2xl',
    'editor' => 'max-w-5xl',
],
```

## Safe Model + Enum Argument Resolution

When modal component public properties are type-hinted:
- Eloquent model IDs are resolved through route binding (for example `user: 5` -> `User $user`)
- backed enums are resolved with `tryFrom`

If a model cannot be resolved, a `ModelNotFoundException` is thrown.

## Prevent Close Dynamically

Active modal can listen for:
- `closingModalOnEscape`
- `closingModalOnClickAway`

Set `payload.closing = false` to cancel close.

## Config Service (Programmatic Access)

```php
$modalConfig = app(\Corepine\Modal\Support\ModalConfig::class);

$openEvent = $modalConfig->listenEvent('open');      // openModal
$closedEvent = $modalConfig->dispatchEvent('closed'); // modalClosed
```

## Backward Compatibility

- `Corepine\Modal\Livewire\ModalComponent` still exists for legacy usage.
- Recommended base class is `Corepine\Modal\Modal`.
- Legacy aliases normalized automatically: `drawer`, `sheet`, `isolated`.

## Testing

```bash
composer test
```

Package tests are written with Pest.
