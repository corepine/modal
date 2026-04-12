# Corepine Modal

Corepine Modal is a stack-based modal system for Laravel with two runtime modes:

- standalone Alpine + Blade modals
- Livewire stack-based modals

- `modal` (dialog)
- `drawer` (left or right panel)
- `sheet` (bottom sheet)

It supports:

- modal stacks (open child modals on top of parent modals)
- declarative shell actions
- strongly typed modal classes (`extends Corepine\Modal\Modal`)
- configurable event names for package-safe integrations
- standalone Blade-only modals (no Livewire modal class required)

## Requirements

- PHP `^8.2|^8.3|^8.4`
- Laravel `^11.0|^12.0|^13.0`
- Livewire `^3.7|^4.0`

Livewire is required by the package because stack mode uses a Livewire host.
Standalone Alpine + Blade usage is still fully supported.

## Installation

```bash
composer require corepine/modal
```

Publish config:

```bash
php artisan vendor:publish --tag=corepine-modal-config
```

## Setup

### Livewire Stack Mode

Render the host once in your layout:

```blade
<x-corepine.modal.assets />
```

Optional: include package CSS directly from the host:

```blade
<x-corepine.modal.assets include-styles />
```

### Standalone Alpine + Blade Mode

You can use `<x-corepine.modal />` directly with browser events and no Livewire modal class.
The host is not required for standalone-only usage.

## Tailwind Setup

Add the package stylesheet to your main CSS entry:

```css
@import "../../vendor/corepine/modal/resources/css/app.css";
```

The package CSS already includes Tailwind `@source` paths for its own views and PHP classes.

## Quick Start (Livewire Stack Mode)

```php
<?php

namespace App\Livewire\Modals;

use App\Models\User;
use Corepine\Modal\Actions\Action;
use Corepine\Modal\Enums\ModalType;
use Corepine\Modal\Modal;
use Corepine\Support\Enums\Placement;

class EditUser extends Modal
{
    public User $user;

    public static function modalAttributes(): array
    {
        return [
            'type' => ModalType::Modal,
            'position' => Placement::Center,
            'origin' => Placement::Center,
            'shell' => true,
            'heading' => 'Edit User',
            'description' => 'Update account details',
            'showClose' => true,
            'dismissible' => true,
            'closeOnEscape' => true,
            'actions' => [
                Action::make('cancel')->label('Cancel')->close(),
                Action::make('save')->label('Save')->primary()->action('save'),
            ],
        ];
    }
}
```

Open it from Blade:

```blade
<button
    type="button"
    onclick="Livewire.dispatch('modal.open', { component: 'modals.edit-user', arguments: { user: {{ $user->id }} } })"
>
    Edit User
</button>
```

## Open / Close APIs

From a modal class:

```php
$this->openModal('modals.edit-user', ['user' => 5]);
$this->openBottomSheet('modals.user-sheet', ['user' => 5]);

$this->closeModal();
$this->closeTopModal(2);
$this->closeAll();
```

From Blade helpers:

```blade
<x-corepine.modal.actions.open component="modals.edit-user" :arguments="['user' => $user->id]">
    <button type="button">Edit</button>
</x-corepine.modal.actions.open>

<x-corepine.modal.actions.close count="1" :destroy="true">
    Close
</x-corepine.modal.actions.close>
```

## Quick Start (Standalone Alpine + Blade Mode)

Use this when you do not need a Livewire modal class:

```blade
<button
    type="button"
    onclick="window.dispatchEvent(new CustomEvent('modal.open', { detail: { id: 'user-sheet' } }))"
>
    Open Sheet
</button>

<x-corepine.modal id="user-sheet" type="sheet" heading="User Details">
    <p class="text-sm text-zinc-600">Standalone modal body</p>

    <x-slot:footer>
        <button
            type="button"
            class="rounded-md border px-3 py-2 text-sm"
            onclick="window.dispatchEvent(new CustomEvent('modal.close', { detail: { id: 'user-sheet' } }))"
        >
            Close
        </button>
    </x-slot:footer>
</x-corepine.modal>
```

Standalone named slots:

- `header`: custom header content. Slot attributes are merged onto the header wrapper classes.
- When `header` slot is provided (even empty), it overrides built-in heading/description/close rendering.
- `footer`: custom footer content.

Example:

```blade
<x-corepine.modal id="custom-header-modal" show-close="false">
    <x-slot:header class="font-bold text-lg" data-testid="custom-header">
        Custom Header
    </x-slot:header>

    <p>Body</p>
</x-corepine.modal>
```

Standalone browser events:

- `modal.open`
- `modal.close`
- `modal.toggle`

Each event accepts `{ id?: string }` in `detail`.

## Modal Attributes

The canonical shell/action API uses `actions` (not legacy keys).

| Key | Type | Default | Notes |
| --- | --- | --- | --- |
| `type` | `modal \| drawer \| sheet` | `modal` | Presentation type. |
| `position` | `Placement \| string` | by type | `modal`: `center/top/bottom/left/right`, `drawer`: `left/right`, `sheet`: forced `bottom`. |
| `origin` | `Placement \| string` | follows type/position | Transform origin; same value set as `position` vocabulary. |
| `size` | `string` | `default` | Width token from config sizes, or custom class string. |
| `height` | `string \| number \| null` | `null` | Panel height (modal/drawer) and initial sheet height. |
| `maxHeight` | `string \| number \| null` | `null` | Shared max-height cap for all types. |
| `dismissible` | `bool` | `true` | Scrim click closes when true. |
| `draggable` | `bool` | type-aware | Sheet drag/resize behavior. |
| `showDragHandle` | `bool` | type-aware | Sheet handle visibility. |
| `dragCloseThreshold` | `float` | `0.3` | Sheet drag-close ratio. |
| `closeOnEscape` | `bool` | `true` | Escape closes top layer. |
| `closeAllOnEscape` | `bool` | `false` | Escape closes full stack. |
| `destroyOnClose` | `bool` | `true` | Remove closed layers from host state. |
| `dispatchCloseEvent` | `bool` | `false` | Emits `modal.component-closed` for that layer. |
| `blur` | `bool` | `false` | Scrim blur effect. |
| `shell` | `bool` | `true` | Enables built-in shell header/body/footer structure. |
| `heading` | `string \| null` | `null` | Shell heading text. |
| `description` | `string \| null` | `null` | Shell description text. |
| `showClose` | `bool` | `true` | Built-in shell close icon. |
| `footerActionsAlignment` | `Alignment \| string` | `end` | `start`, `center`, `end`. |
| `actions` | `array` | `[]` | Declarative shell actions (`close` / `method`). |
| `class` | `string` | `''` | Extra panel classes. |

### Type Behavior Rules

- `sheet`: always renders from bottom and always uses `position=bottom`, `origin=bottom`.
- `drawer`: only `left` and `right` are valid.
- `modal`: supports all five placement values and now fully respects both `position` and `origin`.

## Declarative Actions

Action payloads can be raw arrays or fluent `Action::make(...)` objects.

```php
use Corepine\Modal\Actions\Action;

'actions' => [
    Action::make('cancel')
        ->label('Cancel')
        ->close(),

    Action::make('save')
        ->label('Save')
        ->primary()
        ->action('save'),
]
```

Supported fluent helpers include:

- `method()` / `action()`
- `close(count, destroy, closeAll)`
- `disabled()`
- `visible()`
- `color()` and shortcuts (`primary`, `danger`, `success`, `warning`, `info`, `gray`, `dark`)
- `accent()`
- `outline()`
- `attributes()`

## Event System

Default incoming events:

- `modal.open`
- `modal.open-sheet`
- `modal.close`
- `modal.close-top`
- `modal.close-all`
- `modal.destroy`
- `modal.reset`
- `modal.toggle`

Default outgoing events:

- `modal.opened`
- `modal.closed`
- `modal.changed`
- `modal.all-closed`
- `modal.component-closed`

### Event Customization

You can rename both incoming and outgoing events in `config/corepine-modal.php`:

```php
'events' => [
    'listen' => [
        'open' => 'acme.modal.open',
        'close' => 'acme.modal.close',
    ],
    'dispatch' => [
        'opened' => 'acme.modal.opened',
    ],
],
```

For package integrations, do not hardcode event strings. Resolve them from the service:

```php
use Corepine\Modal\Facades\Modal;

$openEvent = Modal::event()->openModal();
$closeEvent = Modal::event()->closeModal();
```

## Configuration

Main sections in `config/corepine-modal.php`:

- `events.listen`: incoming event names
- `events.dispatch`: outgoing event names
- `defaults.attributes`: global modal attribute defaults
- `sizes`: modal width tokens

### Example Size Override

```php
'sizes' => [
    'default' => 'max-w-xl sm:max-w-full',
    'editor' => 'max-w-[960px]',
],
```

## Blade Components (Canonical)

- `<x-corepine.modal.assets />`
- `<x-corepine.modal />`
- `<x-corepine.modal.layout />`
- `<x-corepine.modal.footer />`
- `<x-corepine.modal.actions.open />`
- `<x-corepine.modal.actions.close />`
