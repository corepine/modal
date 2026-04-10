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
- Use `<x-corepine.modal />` when you want a standalone Blade modal (no Livewire modal host).

## Tailwind v4 Setup

Import package CSS into your main stylesheet:

```css
@import "../../vendor/corepine/modal/resources/css/app.css";
```

No `tailwind.config.js` is required for this package itself.

## Blade Components

Preferred dotted aliases:
- `<x-corepine.modal />`: standalone Alpine Blade modal (no Livewire component required).
- `<x-corepine.modal.assets />`: renders the global Livewire modal host.
- `<x-corepine.modal.layout />`: reusable shell (header/body/footer) for modal content.
- `<x-corepine.modal.template />`: alias of `x-corepine.modal.layout`.
- `<x-corepine.modal.footer />`: inline footer marker for `x-corepine.modal.layout`.
- `<x-corepine.modal.actions.open />`: trigger helper (dispatches open event).
- `<x-corepine.modal.actions.close />`: close helper (dispatches close event).
- `<x-corepine.modal.open />`: alias of `x-corepine.modal.actions.open`.
- `<x-corepine.modal.close />`: alias of `x-corepine.modal.actions.close`.

Legacy aliases still supported:
- `<x-corepine-modal />`, `<x-corepine-modal-layout />`, `<x-corepine-modal-template />`, `<x-corepine-modal-footer />`
- `<x-corepine-modal-actions-open />`, `<x-corepine-modal-actions-close />`
- `<x-corepine-modal-open />`, `<x-corepine-modal-close />`
- `<x-corepine-open-modal />`, `<x-corepine-close-modal />`

## Component Props Reference

### `x-corepine.modal.assets`

Props:
- `includeStyles` (`false` by default): injects `<link rel="stylesheet" href="/vendor/corepine-modal/app.css">`

Use this once in your layout to mount the Livewire host.

## Standalone Blade Modal (`x-corepine.modal`)

Use this when you want a modal without a Livewire modal component stack.

```blade
<x-corepine.modal id="user-sheet" title="User Details" description="Blade-only modal">
    <p class="text-sm text-zinc-600">This modal does not require Livewire host registration.</p>

    <x-slot:footer>
        <button
            type="button"
            class="rounded-md border px-3 py-2 text-sm"
            x-on:click="$dispatch('corepine-modal:close', { id: 'user-sheet' })"
        >
            Close
        </button>
    </x-slot:footer>
</x-corepine.modal>
```

### Standalone Props

| Prop | Type | Default | Notes |
| --- | --- | --- | --- |
| `id` | `string \| null` | `null` | Target key for `corepine-modal:*` browser events. |
| `open` | `bool` | `false` | Initial open state. |
| `title` | `string \| null` | `null` | Optional built-in header title. |
| `description` | `string \| null` | `null` | Optional built-in header description. |
| `showClose` | `bool` | `true` | Shows built-in close icon in header. |
| `modalAttributes` | `array` | `[]` | Raw attribute payload merged with explicit props. |
| `size` | `string` | `default` | Width token or raw width utility classes. |
| `type` | `string \| ModalType \| null` | config default (`modal`) | `modal`, `drawer`, `sheet`. `bottomSheet` / `bottom-sheet` normalize to `sheet`. |
| `drawer` | `bool \| null` | `null` | Legacy alias for `type=drawer`. |
| `sheet` | `bool \| null` | `null` | Legacy alias for `type=sheet`. |
| `bottomSheet` | `bool \| null` | `null` | Friendly alias for `type=sheet`. |
| `position` | `string \| null` | type default | Normalized by type (`center/right/bottom`). Bottom sheets are always forced to `bottom`. |
| `height` | `string \| number \| null` | `null` | Explicit panel height for modal/drawer and initial height for sheet. |
| `sheetHeight` | `string \| number \| null` | `null` | Alias of `height` for sheets. |
| `sheetMinHeight` | `string \| number \| null` | `null` | Sheet minimum height. |
| `minHeight` | `string \| number \| null` | `null` | Alias of `sheetMinHeight`. |
| `sheetMaxHeight` | `string \| number \| null` | `null` | Sheet maximum height. |
| `maxHeight` | `string \| number \| null` | `null` | Alias of `sheetMaxHeight`. |
| `draggable` | `bool \| null` | `null` | For sheet: drag down / resize behavior. |
| `enableDrag` | `bool \| null` | `null` | Alias of `draggable`. |
| `showDragHandle` | `bool \| null` | `null` | For sheet: toggles the visible top drag handle independently of drag behavior. |
| `dragCloseThreshold` | `float \| string \| null` | `null` | For sheet: close threshold ratio (`0.3`). |
| `sheetDragThreshold` | `float \| string \| null` | `null` | Alias of `dragCloseThreshold`. |
| `closeOnEscape` | `bool` | `true` | Escape key closes modal. |
| `closeAllOnEscape` | `bool` | `false` | In stack mode, Escape closes the full stack. Legacy `closeOnEscapeIsForceful` is still supported. |
| `dismissible` | `bool \| null` | `null` | Backdrop/scrim click closes modal when true. |
| `closeOnClickAway` | `bool` | `true` | Legacy alias of `dismissible`. |
| `blur` | `bool` | `false` | Enables backdrop blur style. |
| `class` | `string` | `''` | Merged into modal panel classes. |

Additional non-class attributes are merged onto the panel element.

For bottom sheets:
- The `dismissible` parameter specifies whether the bottom sheet will be dismissed when user taps on the scrim.
- The `enableDrag` parameter specifies whether the bottom sheet can be dragged up and down and dismissed by swiping downwards.

Stack-only behavior is intentionally not included for standalone mode:
- no modal stack tracking
- no previous modal restore
- no close event dispatch chain to other modal layers

Examples:

```html
<button type="button" onclick="window.dispatchEvent(new CustomEvent('corepine-modal:open', { detail: { id: 'user-sheet' } }))">
    Open
</button>
```

### Standalone Browser Events

- `corepine-modal:open`
- `corepine-modal:close`
- `corepine-modal:toggle`

Each event accepts `{ id?: string }` in `detail`. If `id` is omitted, it targets standalone modals with `id=null`.

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
            'layout' => true,
            'type' => ModalType::Modal,
            'title' => 'Edit User',
            'description' => 'Update user details.',
            'showClose' => true,
            'position' => 'center',
            'closeOnEscape' => true,
            'dismissible' => true,
            'closeAllOnEscape' => false,
            'destroyOnClose' => true,
            'dispatchCloseEvent' => false,
            'blur' => false,
            'class' => 'p-6',
        ];
    }
}
```

## Automatic Layout (Native Shell)

Livewire modals now support built-in shell rendering from `modalAttributes`:
- `layout` (`true` by default): use built-in shell.
- `plain` (`false` by default): force raw modal view rendering (no shell).
- `title`, `description`, `showClose`: header chrome options.
- `footerActionsAlignment`: align built-in footer actions (`start`, `center`, `end`).
- `footerActions`: declarative footer actions.

If you need fully custom slot/footer markup, set `plain => true` and render `<x-corepine.modal.layout>` manually inside your component view.

Example:

```php
public static function modalAttributes(): array
{
    return [
        'layout' => true,
        'title' => 'Manage Users',
        'description' => 'Search and view users in your system.',
        'showClose' => true,
        'footerActions' => [
            ['type' => 'close', 'label' => 'Cancel', 'class' => 'rounded-md border px-3 py-2 text-sm'],
            ['type' => 'method', 'method' => 'saveUsers', 'label' => 'Save', 'class' => 'rounded-md bg-zinc-900 px-3 py-2 text-sm text-white'],
        ],
    ];
}
```

`footerActions` supports:
- `type=close`: uses modal close behavior (`count`, `destroy`, `force` are optional).
- `type=method`: calls the active modal Livewire method (`method`, optional `params`).
- `class`: custom classes for the rendered button.
- `buttonType`: HTML button type for method actions (`button`, `submit`, `reset`).
- `disabled`: disables the action.
- `color`: built-in support color name, registered support color alias, or full palette array.
- `outline`: toggles outline styling.
- `attributes`: extra HTML attributes like `data-*`, `aria-*`, or `id`.

Fluent API (`Corepine\Modal\Actions\Action`) is also supported:

```php
use Corepine\Modal\Actions\Action;
use Corepine\Support\Colors\Color;
use Corepine\Support\Facades\CorepineColor;

// For example in a service provider boot() method:
CorepineColor::register([
    'brand' => Color::Fuchsia,
]);

public static function modalAttributes(): array
{
    return [
        'title' => 'Manage Users',
        'footerActions' => [
            Action::make('cancel')
                ->label('Cancel')
                ->gray()
                ->outline()
                ->close(),

            Action::make('saveUsers')
                ->label('Save')
                ->color('brand')
                ->attributes(['data-testid' => 'save-users'])
                ->disabled(fn (): bool => false)
                ->action('saveUsers', [5]),
        ],
    ];
}
```

Notes:
- `action()` and `method()` map to Livewire component methods.
- Fluent actions also support `primary()`, `danger()`, `success()`, `warning()`, `info()`, `gray()`, and `dark()` as shortcuts for `color(...)`.
- When you build actions in PHP with `Action::make(...)`, `disabled()`, `color()`, `outline()`, and `attributes()` can accept closures and will be evaluated server-side.
- Raw array footer actions should stay serializable. Closures are not supported when `footerActions` are sent through the Blade open helper or browser payloads.
- If no custom `class` is provided, modal applies default button styling. If you set `color()` or `outline()`, the preset action styles are used and your custom classes are merged in.

## Layout Shell (`x-corepine.modal.layout`)

Use this inside your modal view so you do not repeat title/close/footer structure.

Props:

| Prop | Type | Default | Notes |
| --- | --- | --- | --- |
| `title` | `string \| null` | `null` | Header title. |
| `description` | `string \| null` | `null` | Header description text. |
| `showClose` | `bool` | `true` | Shows `x-corepine.modal.close` button. |
| `class` | `string` | `''` | Merged onto root layout wrapper. |

Named slot:
- `footer` (optional)

Inline alternative:
- Use `<x-corepine.modal.footer>...</x-corepine.modal.footer>` anywhere inside layout body content.
- The layout extracts it server-side and renders it in the real footer region (no sticky/teleport hack).
- You can pass normal attributes (for example `id="users-footer-actions"` or custom classes) and they are preserved on the rendered footer wrapper.
- If both are provided, named `x-slot:footer` takes precedence.

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

Inline footer marker example:

```blade
<x-corepine.modal.layout title="Manage Users">
    <div class="space-y-3">
        <!-- body content -->
    </div>

    <x-corepine.modal.footer>
        <div class="flex justify-end gap-2">
            <x-corepine.modal.actions.close class="rounded-md border px-3 py-2 text-sm">Cancel</x-corepine.modal.actions.close>
            <button type="button" class="rounded-md bg-zinc-900 px-3 py-2 text-sm text-white">Save</button>
        </div>
    </x-corepine.modal.footer>
</x-corepine.modal.layout>
```

Scrollable body tip:

When your content includes a list/table that should scroll inside the modal body (while footer stays visible), make the first slot wrapper a flex column with `min-h-0` and put `overflow-y-auto` on the inner list container.

```blade
<x-corepine.modal.layout title="Manage Users">
    <div class="flex h-full min-h-0 flex-col gap-3">
        <input type="text" class="..." />

        <div class="min-h-0 flex-1 overflow-y-auto rounded-lg border">
            <!-- scrollable list -->
        </div>
    </div>

    <x-slot:footer>
        <div class="flex justify-end gap-2">
            <x-corepine.modal.actions.close class="rounded-md border px-3 py-2 text-sm">Cancel</x-corepine.modal.actions.close>
            <button type="button" class="rounded-md bg-zinc-900 px-3 py-2 text-sm text-white">Save</button>
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

### `x-corepine.modal.actions.open` Props

| Prop | Type | Default | Notes |
| --- | --- | --- | --- |
| `component` | `string \| null` | `null` | Livewire component name (`modals.edit-user`). |
| `componentClass` | `class-string \| null` | `null` | Livewire component class path. Takes priority over `component`. |
| `arguments` | `array` | `[]` | Payload passed to modal component mount/properties. |
| `modalAttributes` | `array` | `[]` | Raw modal attribute payload. |
| `size` | `string \| null` | `null` | Width token/raw classes. |
| `height` | `string \| number \| null` | `null` | Explicit panel height for modal/drawer and initial height for sheet. |
| `blur` | `bool \| string \| null` | `null` | Backdrop blur toggle. |
| `type` | `string \| ModalType \| null` | `null` | `modal`, `drawer`, `sheet`. |
| `drawer` | `bool \| string \| null` | `null` | Legacy alias to set drawer type. |
| `sheet` | `bool \| string \| null` | `null` | Legacy alias to set sheet type. |
| `position` | `string \| null` | `null` | Position normalized by type rules. |
| `isolate` | `bool \| string \| null` | `null` | Keep previous modal layers visible. |
| `isolated` | `bool \| string \| null` | `null` | Legacy alias for `isolate`. |
| `layout` | `bool \| string \| null` | `null` | Enable built-in shell rendering. |
| `plain` | `bool \| string \| null` | `null` | Disable built-in shell rendering. |
| `title` | `string \| null` | `null` | Shell header title when layout is enabled. |
| `description` | `string \| null` | `null` | Shell header description when layout is enabled. |
| `showClose` | `bool \| string \| null` | `null` | Toggle shell close button. |
| `footerActions` | `array` | `[]` | Declarative shell footer actions. |
| `class` | `string` | `''` | Forwarded into modal panel classes, not wrapper class. |

Wrapper behavior:
- non-class attributes (`id`, `data-*`, etc.) stay on trigger wrapper.
- class attributes are intentionally moved into modal panel class payload.

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

### `x-corepine.modal.actions.close` Props

| Prop | Type | Default | Notes |
| --- | --- | --- | --- |
| `count` | `int` | `1` | How many layers to close (min `1`). |
| `destroy` | `bool` | `true` | Remove closed modal instances from stack storage. |
| `force` | `bool` | `false` | Close all layers instead of top `count`. |
| `disabled` | `bool` | `false` | Renders the close action as disabled and suppresses click handling. |

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
- Position is always forced to `bottom`.
- Top handle can resize sheet height.
- You can drag the sheet downward to close.
- Drag-close threshold defaults to `30%` of current sheet height.
- Sheet closes through normal close events, so stack behavior remains consistent.

Height defaults by type:
- `modal`: CSS class `50dvh` (`cp-modal-panel-default-height`)
- `drawer`: CSS class `100dvh` (`cp-modal-panel-drawer-height`)
- `sheet`: runtime default `70dvh`

Use `height` when you want explicit height precedence on all types (`modal`, `drawer`, `sheet`).
For responsive behavior, use panel classes (`class` / `modalAttributes.class`) like `h-full md:h-[600px]`.

For sheets, precedence order is:
1. `height`
2. `sheetHeight` (legacy alias)
3. class height hints (`h-[...]`, `h-full`, `h-screen`, fractions)

Recommended sheet config:

```php
public static function modalAttributes(): array
{
    return [
        'bottomSheet' => true,
        'dismissible' => true,
        'draggable' => true,
        'showDragHandle' => true,
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

Sheet class-height hints supported:
- `h-[...]`
- `h-full`, `h-screen`, `h-dvh`, `h-svh`, `h-lvh`
- fractions like `h-3/4`

Note: `height` is converted to inline style, so responsive strings like `h-full md:h-[600px]` should be placed in `class`, not `height`.

## Complete Modal Attributes Reference

The table below covers all supported modal attributes (including legacy aliases and sheet-specific runtime options).

| Attribute | Type | Default | Applies To | Notes |
| --- | --- | --- | --- | --- |
| `closeOnEscape` | bool | `true` | all | Escape key closes active modal when true. |
| `closeAllOnEscape` | bool | `false` | all | Escape closes the full modal stack when true. |
| `dismissible` | bool | `true` | all | Backdrop/scrim click closes the active modal when true. |
| `closeOnClickAway` | bool | alias | all | Legacy alias of `dismissible`. |
| `destroyOnClose` | bool | `true` | all | Destroy component state when modal closes. |
| `dispatchCloseEvent` | bool | `false` | all | Dispatches `modalComponentClosed` when closing this component. |
| `blur` | bool | `false` | all | Adds blurred backdrop style. |
| `type` | string/enum | `modal` | all | `modal`, `drawer`, `sheet`. `bottomSheet` / `bottom-sheet` normalize to `sheet`. |
| `drawer` | bool | `false` | all | Legacy alias for `type=drawer`. |
| `sheet` | bool | `false` | all | Legacy alias for `type=sheet`. |
| `bottomSheet` | bool | `false` | all | Alias for `type=sheet`. |
| `isolate` | bool | `false` | all | Keeps previous layers visible behind active modal. |
| `isolated` | bool | n/a | all | Legacy alias of `isolate`. |
| `layout` | bool | `true` | Livewire host | Enables built-in shell (`x-corepine.modal.layout`) wrapping. |
| `plain` | bool | `false` | Livewire host | Disables built-in shell and renders raw modal view. |
| `title` | string/null | `null` | layout | Shell header title. |
| `description` | string/null | `null` | layout | Shell header description. |
| `showClose` | bool | `true` | layout | Shell close button visibility. |
| `footerActionsAlignment` | string/enum | `end` | layout | Aligns built-in footer actions. Accepts `start`, `center`, `end`, `right`, `left`, or `Corepine\Support\Enums\Alignment`. |
| `footerActions` | array | `[]` | layout | Declarative footer actions (`close` / `method`). |
| `position` | string | type-based | all | Normalized per type rules. Bottom sheets are always `bottom`. |
| `size` | string | `default` | all | Token from config sizes or raw width classes. |
| `height` | string/number | `null` | all | Explicit panel height for modal/drawer and initial sheet height (`vh`, `dvh`, `%`, `px`, numeric ratios, or `h-[...]`/`h-full` style tokens). |
| `class` | string | `''` | all | Extra classes merged on modal surface (`cp-modal-component`). |
| `draggable` | bool | `true` for sheet | sheet | Enables sheet drag/resize behavior. |
| `enableDrag` | bool | alias | sheet | Alias of `draggable`. |
| `showDragHandle` | bool | `true` for draggable sheets | sheet | Shows or hides the top drag handle without disabling drag itself. |
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
