<?php

namespace Corepine\Modal\Actions;

use Closure;
use Corepine\Support\Colors\Color as SupportColor;
use Corepine\Support\Facades\CorepineColor;

class Action
{
    private string $name;

    private string $type = 'method';

    private ?string $label = null;

    private ?string $method = null;

    /**
     * @var array<int, mixed>
     */
    private array $params = [];

    private string $class = '';

    private string $buttonType = 'button';

    private bool | Closure $disabled = false;

    private bool | Closure $visible = true;

    private array | string | Closure | null $color = null;

    private bool | Closure | null $accent = null;

    private bool | Closure | null $outline = null;

    /**
     * @var array<string, mixed>|Closure
     */
    private array | Closure $attributes = [];

    private int $count = 1;

    private bool $destroy = true;

    private bool $closeAll = false;

    private function __construct(string $name)
    {
        $name = trim($name);
        $this->name = $name === '' ? 'action' : $name;
    }

    public static function make(string $name): self
    {
        return new self($name);
    }

    public function label(string $label): self
    {
        $label = trim($label);
        $this->label = $label === '' ? null : $label;

        return $this;
    }

    public function class(string $class): self
    {
        $this->class = trim($class);

        return $this;
    }

    /**
     * @param  array<int, mixed>  $params
     */
    public function method(?string $method = null, array $params = []): self
    {
        $this->type = 'method';

        $resolved = is_string($method) ? trim($method) : '';
        $this->method = $resolved !== '' ? $resolved : $this->name;
        $this->params = array_values($params);

        return $this;
    }

    /**
     * @param  array<int, mixed>  $params
     */
    public function action(?string $method = null, array $params = []): self
    {
        return $this->method($method, $params);
    }

    /**
     * @param  array<int, mixed>  $params
     */
    public function params(array $params): self
    {
        $this->params = array_values($params);

        return $this;
    }

    public function buttonType(string $buttonType): self
    {
        $normalized = strtolower(trim($buttonType));

        if (in_array($normalized, ['button', 'submit', 'reset'], true)) {
            $this->buttonType = $normalized;
        }

        return $this;
    }

    public function disabled(bool | Closure $condition = true): self
    {
        $this->disabled = $condition;

        return $this;
    }

    public function visible(bool | Closure $condition = true): self
    {
        $this->visible = $condition;

        return $this;
    }

    public function color(array | string | Closure | null $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function primary(): self
    {
        return $this->color('primary');
    }

    public function danger(): self
    {
        return $this->color('danger');
    }

    public function success(): self
    {
        return $this->color('success');
    }

    public function warning(): self
    {
        return $this->color('warning');
    }

    public function info(): self
    {
        return $this->color('info');
    }

    public function gray(): self
    {
        return $this->color('gray');
    }

    public function dark(): self
    {
        return $this->color('dark');
    }

    public function accent(bool | Closure $condition = true): self
    {
        $this->accent = $condition;

        return $this;
    }

    public function accented(bool | Closure $condition = true): self
    {
        return $this->accent($condition);
    }

    public function outline(bool | Closure $condition = true): self
    {
        $this->outline = $condition;

        return $this;
    }

    public function outlined(bool | Closure $condition = true): self
    {
        return $this->outline($condition);
    }

    /**
     * @param  array<string, mixed>|Closure  $attributes
     */
    public function attributes(array | Closure $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param  array<string, mixed>|Closure  $attributes
     */
    public function extraAttributes(array | Closure $attributes): self
    {
        return $this->attributes($attributes);
    }

    public function attribute(string $name, mixed $value = true): self
    {
        $name = trim($name);

        if ($name === '') {
            return $this;
        }

        $attributes = $this->evaluate($this->attributes);

        if (! is_array($attributes)) {
            $attributes = [];
        }

        $attributes[$name] = $value;
        $this->attributes = $attributes;

        return $this;
    }

    public function close(int $count = 1, bool $destroy = true, bool $closeAll = false): self
    {
        $this->type = 'close';
        $this->count = max(1, $count);
        $this->destroy = $destroy;
        $this->closeAll = $closeAll;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $disabled = (bool) $this->evaluate($this->disabled);
        $visible = (bool) $this->evaluate($this->visible);
        $rawColor = $this->evaluate($this->color);
        $color = $this->resolveColor($rawColor);
        $paletteName = $this->resolveColorName($rawColor, $color);
        $accent = $this->accent instanceof Closure || is_bool($this->accent)
            ? (bool) $this->evaluate($this->accent)
            : false;
        $outlineDefault = $this->type === 'close' || $color === null;
        $outline = $this->outline instanceof Closure || is_bool($this->outline)
            ? (bool) $this->evaluate($this->outline)
            : $outlineDefault;
        $attributes = $this->evaluate($this->attributes);

        if (! is_array($attributes)) {
            $attributes = [];
        }

        if ($this->type === 'close') {
            return [
                'type' => 'close',
                'label' => $this->label ?? 'Close',
                'class' => $this->resolveClass($this->class, $paletteName, $outline, $accent, $disabled),
                'style' => $this->resolveStyle($color, $paletteName, $outline),
                'disabled' => $disabled,
                'visible' => $visible,
                'color' => $color,
                'outline' => $outline,
                'accent' => $accent,
                'attributes' => $attributes,
                'count' => $this->count,
                'destroy' => $this->destroy,
                'closeAll' => $this->closeAll,
                'resolved' => true,
            ];
        }

        $method = $this->method;

        if (! is_string($method) || trim($method) === '') {
            $method = $this->name;
        }

        return [
            'type' => 'method',
            'label' => $this->label ?? ucwords(str_replace(['-', '_'], ' ', $this->name)),
            'method' => $method,
            'params' => $this->params,
            'class' => $this->resolveClass($this->class, $paletteName, $outline, $accent, $disabled),
            'disabled' => $disabled,
            'visible' => $visible,
            'style' => $this->resolveStyle($color, $paletteName, $outline),
            'color' => $color,
            'outline' => $outline,
            'accent' => $accent,
            'attributes' => $attributes,
            'buttonType' => $this->buttonType,
            'resolved' => true,
        ];
    }

    private function resolveColor(mixed $color): array | null
    {
        if (is_array($color)) {
            $normalized = [];

            foreach ($color as $shade => $value) {
                if (! is_string($value)) {
                    continue;
                }

                $value = trim($value);

                if ($value === '') {
                    continue;
                }

                $normalized[is_int($shade) || ! ctype_digit((string) $shade) ? $shade : (int) $shade] = $value;
            }

            if ($normalized === []) {
                return null;
            }

            ksort($normalized);

            return $normalized;
        }

        if (! is_string($color)) {
            return null;
        }

        $color = trim($color);

        if ($color === '') {
            return null;
        }

        $primaryPalette = CorepineColor::palette($color);

        if (is_array($primaryPalette)) {
            return $primaryPalette;
        }

        return match (strtolower($color)) {
            'primary' => CorepineColor::palette('primary') ?? SupportColor::Blue,
            'danger' => SupportColor::Red,
            'success' => SupportColor::Green,
            'warning' => SupportColor::Yellow,
            'info' => SupportColor::Sky,
            'gray' => SupportColor::Gray,
            'dark' => SupportColor::Zinc,
            default => null,
        };
    }

    /**
     * @param  array<int|string, string>  $palette
     */
    private function footerActionSolidTextColor(array $palette): string
    {
        $baseColor = $this->paletteShade($palette, 500) ?? '';

        return $this->isLightFooterActionColor($baseColor) ? '#18181b' : '#ffffff';
    }

    /**
     * @param  array<int|string, string>  $palette
     */
    private function paletteShade(array $palette, int $shade): ?string
    {
        return $palette[$shade] ?? $palette[500] ?? null;
    }

    private function isLightFooterActionColor(string $color): bool
    {
        if (preg_match('/^oklch\(\s*([0-9.]+)/i', $color, $matches) === 1) {
            return (float) ($matches[1] ?? 0) >= 0.72;
        }

        if (preg_match('/^#([0-9a-f]{6})$/i', $color, $matches) === 1) {
            $hex = $matches[1];
            $red = hexdec(substr($hex, 0, 2));
            $green = hexdec(substr($hex, 2, 2));
            $blue = hexdec(substr($hex, 4, 2));

            return ((0.299 * $red) + (0.587 * $green) + (0.114 * $blue)) / 255 >= 0.65;
        }

        return false;
    }

    private function resolveColorName(mixed $rawColor, ?array $palette): ?string
    {
        $catalog = SupportColor::catalog();

        if (is_array($palette)) {
            foreach ($catalog as $name => $builtInPalette) {
                if ($builtInPalette === $palette) {
                    return $name;
                }
            }
        }

        if (! is_string($rawColor)) {
            return null;
        }

        $name = strtolower(trim($rawColor));

        if ($name === '') {
            return null;
        }

        return match ($name) {
            'primary' => $this->matchPrimaryPalette(),
            'danger' => 'red',
            'success' => 'green',
            'warning' => 'yellow',
            'info' => 'sky',
            'gray' => 'gray',
            'dark' => 'zinc',
            default => $palette !== null ? $this->matchPaletteName($palette) : null,
        };
    }

    private function matchPrimaryPalette(): ?string
    {
        $palette = CorepineColor::palette('primary');

        if (! is_array($palette)) {
            return null;
        }

        return $this->matchPaletteName($palette);
    }

    /**
     * @param  array<int|string, string>  $palette
     */
    private function matchPaletteName(array $palette): ?string
    {
        foreach (SupportColor::catalog() as $name => $builtInPalette) {
            if ($builtInPalette === $palette) {
                return $name;
            }
        }

        return null;
    }

    private function resolveClass(string $class, ?string $paletteName, bool $outline, bool $accent, bool $disabled): string
    {
        $paletteClass = $this->resolvePaletteClasses($paletteName, $outline, $accent, $disabled);

        return trim(implode(' ', array_filter([
            'cp-modal-action',
            $paletteClass,
            $disabled ? 'cp-modal-action-disabled' : '',
            $disabled ? 'cursor-not-allowed' : '',
            trim($class),
        ])));
    }

    private function resolvePaletteClasses(?string $paletteName, bool $outline, bool $accent, bool $disabled): string
    {
        if (! is_string($paletteName) || trim($paletteName) === '') {
            return '';
        }

        if ($disabled) {
            return match (strtolower(trim($paletteName))) {
                'red' => $outline ? 'bg-transparent border-red-200 text-red-700 dark:bg-transparent dark:border-red-700 dark:text-red-200' : ($accent ? '!bg-red-50 !border-red-200 !text-red-700 dark:!bg-red-950 dark:!border-red-800 dark:!text-red-100' : 'bg-red-600 border-red-600 text-white dark:bg-red-500 dark:border-red-500 dark:text-white'),
                'green' => $outline ? 'bg-transparent border-green-200 text-green-700 dark:bg-transparent dark:border-green-700 dark:text-green-200' : ($accent ? '!bg-green-50 !border-green-200 !text-green-700 dark:!bg-green-950 dark:!border-green-800 dark:!text-green-100' : 'bg-green-600 border-green-600 text-white dark:bg-green-500 dark:border-green-500 dark:text-white'),
                'yellow' => $outline ? 'bg-transparent border-yellow-200 text-yellow-700 dark:bg-transparent dark:border-yellow-700 dark:text-yellow-200' : ($accent ? '!bg-yellow-50 !border-yellow-200 !text-yellow-700 dark:!bg-yellow-950 dark:!border-yellow-800 dark:!text-yellow-100' : 'bg-yellow-600 border-yellow-600 text-white dark:bg-yellow-500 dark:border-yellow-500 dark:text-white'),
                'sky' => $outline ? 'bg-transparent border-sky-200 text-sky-700 dark:bg-transparent dark:border-sky-700 dark:text-sky-200' : ($accent ? '!bg-sky-50 !border-sky-200 !text-sky-700 dark:!bg-sky-950 dark:!border-sky-800 dark:!text-sky-100' : 'bg-sky-600 border-sky-600 text-white dark:bg-sky-500 dark:border-sky-500 dark:text-white'),
                'gray' => $outline ? 'bg-transparent border-gray-200 text-gray-700 dark:bg-transparent dark:border-gray-700 dark:text-gray-200' : ($accent ? '!bg-gray-50 !border-gray-200 !text-gray-700 dark:!bg-gray-950 dark:!border-gray-800 dark:!text-gray-100' : 'bg-gray-600 border-gray-600 text-white dark:bg-gray-500 dark:border-gray-500 dark:text-white'),
                'zinc' => $outline ? 'bg-transparent border-zinc-200 text-zinc-700 dark:bg-transparent dark:border-zinc-700 dark:text-zinc-200' : ($accent ? '!bg-zinc-50 !border-zinc-200 !text-zinc-700 dark:!bg-zinc-950 dark:!border-zinc-800 dark:!text-zinc-100' : 'bg-zinc-600 border-zinc-600 text-white dark:bg-zinc-500 dark:border-zinc-500 dark:text-white'),
                'blue' => $outline ? 'bg-transparent border-blue-200 text-blue-700 dark:bg-transparent dark:border-blue-700 dark:text-blue-200' : ($accent ? '!bg-blue-50 !border-blue-200 !text-blue-700 dark:!bg-blue-950 dark:!border-blue-800 dark:!text-blue-100' : 'bg-blue-600 border-blue-600 text-white dark:bg-blue-500 dark:border-blue-500 dark:text-white'),
                'amber' => $outline ? 'bg-transparent border-amber-200 text-amber-700 dark:bg-transparent dark:border-amber-700 dark:text-amber-200' : ($accent ? '!bg-amber-50 !border-amber-200 !text-amber-700 dark:!bg-amber-950 dark:!border-amber-800 dark:!text-amber-100' : 'bg-amber-600 border-amber-600 text-white dark:bg-amber-500 dark:border-amber-500 dark:text-white'),
                'fuchsia' => $outline ? 'bg-transparent border-fuchsia-200 text-fuchsia-700 dark:bg-transparent dark:border-fuchsia-700 dark:text-fuchsia-200' : ($accent ? '!bg-fuchsia-50 !border-fuchsia-200 !text-fuchsia-700 dark:!bg-fuchsia-950 dark:!border-fuchsia-800 dark:!text-fuchsia-100' : 'bg-fuchsia-600 border-fuchsia-600 text-white dark:bg-fuchsia-500 dark:border-fuchsia-500 dark:text-white'),
                'purple' => $outline ? 'bg-transparent border-purple-200 text-purple-700 dark:bg-transparent dark:border-purple-700 dark:text-purple-200' : ($accent ? '!bg-purple-50 !border-purple-200 !text-purple-700 dark:!bg-purple-950 dark:!border-purple-800 dark:!text-purple-100' : 'bg-purple-600 border-purple-600 text-white dark:bg-purple-500 dark:border-purple-500 dark:text-white'),
                'pink' => $outline ? 'bg-transparent border-pink-200 text-pink-700 dark:bg-transparent dark:border-pink-700 dark:text-pink-200' : ($accent ? '!bg-pink-50 !border-pink-200 !text-pink-700 dark:!bg-pink-950 dark:!border-pink-800 dark:!text-pink-100' : 'bg-pink-600 border-pink-600 text-white dark:bg-pink-500 dark:border-pink-500 dark:text-white'),
                'rose' => $outline ? 'bg-transparent border-rose-200 text-rose-700 dark:bg-transparent dark:border-rose-700 dark:text-rose-200' : ($accent ? '!bg-rose-50 !border-rose-200 !text-rose-700 dark:!bg-rose-950 dark:!border-rose-800 dark:!text-rose-100' : 'bg-rose-600 border-rose-600 text-white dark:bg-rose-500 dark:border-rose-500 dark:text-white'),
                'indigo' => $outline ? 'bg-transparent border-indigo-200 text-indigo-700 dark:bg-transparent dark:border-indigo-700 dark:text-indigo-200' : ($accent ? '!bg-indigo-50 !border-indigo-200 !text-indigo-700 dark:!bg-indigo-950 dark:!border-indigo-800 dark:!text-indigo-100' : 'bg-indigo-600 border-indigo-600 text-white dark:bg-indigo-500 dark:border-indigo-500 dark:text-white'),
                'teal' => $outline ? 'bg-transparent border-teal-200 text-teal-700 dark:bg-transparent dark:border-teal-700 dark:text-teal-200' : ($accent ? '!bg-teal-50 !border-teal-200 !text-teal-700 dark:!bg-teal-950 dark:!border-teal-800 dark:!text-teal-100' : 'bg-teal-600 border-teal-600 text-white dark:bg-teal-500 dark:border-teal-500 dark:text-white'),
                'cyan' => $outline ? 'bg-transparent border-cyan-200 text-cyan-700 dark:bg-transparent dark:border-cyan-700 dark:text-cyan-200' : ($accent ? '!bg-cyan-50 !border-cyan-200 !text-cyan-700 dark:!bg-cyan-950 dark:!border-cyan-800 dark:!text-cyan-100' : 'bg-cyan-600 border-cyan-600 text-white dark:bg-cyan-500 dark:border-cyan-500 dark:text-white'),
                'emerald' => $outline ? 'bg-transparent border-emerald-200 text-emerald-700 dark:bg-transparent dark:border-emerald-700 dark:text-emerald-200' : ($accent ? '!bg-emerald-50 !border-emerald-200 !text-emerald-700 dark:!bg-emerald-950 dark:!border-emerald-800 dark:!text-emerald-100' : 'bg-emerald-600 border-emerald-600 text-white dark:bg-emerald-500 dark:border-emerald-500 dark:text-white'),
                default => '',
            };
        }

        return match (strtolower(trim($paletteName))) {
            'red' => $outline ? 'bg-transparent border-red-200 text-red-700 hover:bg-red-50 hover:border-red-300 hover:text-red-800 dark:bg-transparent dark:border-red-700 dark:text-red-200 dark:hover:bg-red-950 dark:hover:border-red-600 dark:hover:text-red-100' : ($accent ? '!bg-red-50 !border-red-200 !text-red-700 hover:!bg-red-100 hover:!border-red-300 hover:!text-red-800 dark:!bg-red-950 dark:!border-red-800 dark:!text-red-100 dark:hover:!bg-red-900 dark:hover:!border-red-700 dark:hover:!text-white' : 'bg-red-600 border-red-600 text-white hover:bg-red-500 hover:border-red-500 dark:bg-red-500 dark:border-red-500 dark:text-white dark:hover:bg-red-600 dark:hover:border-red-600'),
            'green' => $outline ? 'bg-transparent border-green-200 text-green-700 hover:bg-green-50 hover:border-green-300 hover:text-green-800 dark:bg-transparent dark:border-green-700 dark:text-green-200 dark:hover:bg-green-950 dark:hover:border-green-600 dark:hover:text-green-100' : ($accent ? '!bg-green-50 !border-green-200 !text-green-700 hover:!bg-green-100 hover:!border-green-300 hover:!text-green-800 dark:!bg-green-950 dark:!border-green-800 dark:!text-green-100 dark:hover:!bg-green-900 dark:hover:!border-green-700 dark:hover:!text-white' : 'bg-green-600 border-green-600 text-white hover:bg-green-500 hover:border-green-500 dark:bg-green-500 dark:border-green-500 dark:text-white dark:hover:bg-green-600 dark:hover:border-green-600'),
            'yellow' => $outline ? 'bg-transparent border-yellow-200 text-yellow-700 hover:bg-yellow-50 hover:border-yellow-300 hover:text-yellow-800 dark:bg-transparent dark:border-yellow-700 dark:text-yellow-200 dark:hover:bg-yellow-950 dark:hover:border-yellow-600 dark:hover:text-yellow-100' : ($accent ? '!bg-yellow-50 !border-yellow-200 !text-yellow-700 hover:!bg-yellow-100 hover:!border-yellow-300 hover:!text-yellow-800 dark:!bg-yellow-950 dark:!border-yellow-800 dark:!text-yellow-100 dark:hover:!bg-yellow-900 dark:hover:!border-yellow-700 dark:hover:!text-white' : 'bg-yellow-600 border-yellow-600 text-white hover:bg-yellow-500 hover:border-yellow-500 dark:bg-yellow-500 dark:border-yellow-500 dark:text-white dark:hover:bg-yellow-600 dark:hover:border-yellow-600'),
            'sky' => $outline ? 'bg-transparent border-sky-200 text-sky-700 hover:bg-sky-50 hover:border-sky-300 hover:text-sky-800 dark:bg-transparent dark:border-sky-700 dark:text-sky-200 dark:hover:bg-sky-950 dark:hover:border-sky-600 dark:hover:text-sky-100' : ($accent ? '!bg-sky-50 !border-sky-200 !text-sky-700 hover:!bg-sky-100 hover:!border-sky-300 hover:!text-sky-800 dark:!bg-sky-950 dark:!border-sky-800 dark:!text-sky-100 dark:hover:!bg-sky-900 dark:hover:!border-sky-700 dark:hover:!text-white' : 'bg-sky-600 border-sky-600 text-white hover:bg-sky-500 hover:border-sky-500 dark:bg-sky-500 dark:border-sky-500 dark:text-white dark:hover:bg-sky-600 dark:hover:border-sky-600'),
            'gray' => $outline ? 'bg-transparent border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 dark:bg-transparent dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-950 dark:hover:border-gray-600 dark:hover:text-gray-100' : ($accent ? '!bg-gray-50 !border-gray-200 !text-gray-700 hover:!bg-gray-100 hover:!border-gray-300 hover:!text-gray-800 dark:!bg-gray-950 dark:!border-gray-800 dark:!text-gray-100 dark:hover:!bg-gray-900 dark:hover:!border-gray-700 dark:hover:!text-white' : 'bg-gray-600 border-gray-600 text-white hover:bg-gray-500 hover:border-gray-500 dark:bg-gray-500 dark:border-gray-500 dark:text-white dark:hover:bg-gray-600 dark:hover:border-gray-600'),
            'zinc' => $outline ? 'bg-transparent border-zinc-200 text-zinc-700 hover:bg-zinc-50 hover:border-zinc-300 hover:text-zinc-800 dark:bg-transparent dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-950 dark:hover:border-zinc-600 dark:hover:text-zinc-100' : ($accent ? '!bg-zinc-50 !border-zinc-200 !text-zinc-700 hover:!bg-zinc-100 hover:!border-zinc-300 hover:!text-zinc-800 dark:!bg-zinc-950 dark:!border-zinc-800 dark:!text-zinc-100 dark:hover:!bg-zinc-900 dark:hover:!border-zinc-700 dark:hover:!text-white' : 'bg-zinc-600 border-zinc-600 text-white hover:bg-zinc-500 hover:border-zinc-500 dark:bg-zinc-500 dark:border-zinc-500 dark:text-white dark:hover:bg-zinc-600 dark:hover:border-zinc-600'),
            'blue' => $outline ? 'bg-transparent border-blue-200 text-blue-700 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-800 dark:bg-transparent dark:border-blue-700 dark:text-blue-200 dark:hover:bg-blue-950 dark:hover:border-blue-600 dark:hover:text-blue-100' : ($accent ? '!bg-blue-50 !border-blue-200 !text-blue-700 hover:!bg-blue-100 hover:!border-blue-300 hover:!text-blue-800 dark:!bg-blue-950 dark:!border-blue-800 dark:!text-blue-100 dark:hover:!bg-blue-900 dark:hover:!border-blue-700 dark:hover:!text-white' : 'bg-blue-600 border-blue-600 text-white hover:bg-blue-500 hover:border-blue-500 dark:bg-blue-500 dark:border-blue-500 dark:text-white dark:hover:bg-blue-600 dark:hover:border-blue-600'),
            'amber' => $outline ? 'bg-transparent border-amber-200 text-amber-700 hover:bg-amber-50 hover:border-amber-300 hover:text-amber-800 dark:bg-transparent dark:border-amber-700 dark:text-amber-200 dark:hover:bg-amber-950 dark:hover:border-amber-600 dark:hover:text-amber-100' : ($accent ? '!bg-amber-50 !border-amber-200 !text-amber-700 hover:!bg-amber-100 hover:!border-amber-300 hover:!text-amber-800 dark:!bg-amber-950 dark:!border-amber-800 dark:!text-amber-100 dark:hover:!bg-amber-900 dark:hover:!border-amber-700 dark:hover:!text-white' : 'bg-amber-600 border-amber-600 text-white hover:bg-amber-500 hover:border-amber-500 dark:bg-amber-500 dark:border-amber-500 dark:text-white dark:hover:bg-amber-600 dark:hover:border-amber-600'),
            'fuchsia' => $outline ? 'bg-transparent border-fuchsia-200 text-fuchsia-700 hover:bg-fuchsia-50 hover:border-fuchsia-300 hover:text-fuchsia-800 dark:bg-transparent dark:border-fuchsia-700 dark:text-fuchsia-200 dark:hover:bg-fuchsia-950 dark:hover:border-fuchsia-600 dark:hover:text-fuchsia-100' : ($accent ? '!bg-fuchsia-50 !border-fuchsia-200 !text-fuchsia-700 hover:!bg-fuchsia-100 hover:!border-fuchsia-300 hover:!text-fuchsia-800 dark:!bg-fuchsia-950 dark:!border-fuchsia-800 dark:!text-fuchsia-100 dark:hover:!bg-fuchsia-900 dark:hover:!border-fuchsia-700 dark:hover:!text-white' : 'bg-fuchsia-600 border-fuchsia-600 text-white hover:bg-fuchsia-500 hover:border-fuchsia-500 dark:bg-fuchsia-500 dark:border-fuchsia-500 dark:text-white dark:hover:bg-fuchsia-600 dark:hover:border-fuchsia-600'),
            'purple' => $outline ? 'bg-transparent border-purple-200 text-purple-700 hover:bg-purple-50 hover:border-purple-300 hover:text-purple-800 dark:bg-transparent dark:border-purple-700 dark:text-purple-200 dark:hover:bg-purple-950 dark:hover:border-purple-600 dark:hover:text-purple-100' : ($accent ? '!bg-purple-50 !border-purple-200 !text-purple-700 hover:!bg-purple-100 hover:!border-purple-300 hover:!text-purple-800 dark:!bg-purple-950 dark:!border-purple-800 dark:!text-purple-100 dark:hover:!bg-purple-900 dark:hover:!border-purple-700 dark:hover:!text-white' : 'bg-purple-600 border-purple-600 text-white hover:bg-purple-500 hover:border-purple-500 dark:bg-purple-500 dark:border-purple-500 dark:text-white dark:hover:bg-purple-600 dark:hover:border-purple-600'),
            'pink' => $outline ? 'bg-transparent border-pink-200 text-pink-700 hover:bg-pink-50 hover:border-pink-300 hover:text-pink-800 dark:bg-transparent dark:border-pink-700 dark:text-pink-200 dark:hover:bg-pink-950 dark:hover:border-pink-600 dark:hover:text-pink-100' : ($accent ? '!bg-pink-50 !border-pink-200 !text-pink-700 hover:!bg-pink-100 hover:!border-pink-300 hover:!text-pink-800 dark:!bg-pink-950 dark:!border-pink-800 dark:!text-pink-100 dark:hover:!bg-pink-900 dark:hover:!border-pink-700 dark:hover:!text-white' : 'bg-pink-600 border-pink-600 text-white hover:bg-pink-500 hover:border-pink-500 dark:bg-pink-500 dark:border-pink-500 dark:text-white dark:hover:bg-pink-600 dark:hover:border-pink-600'),
            'rose' => $outline ? 'bg-transparent border-rose-200 text-rose-700 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-800 dark:bg-transparent dark:border-rose-700 dark:text-rose-200 dark:hover:bg-rose-950 dark:hover:border-rose-600 dark:hover:text-rose-100' : ($accent ? '!bg-rose-50 !border-rose-200 !text-rose-700 hover:!bg-rose-100 hover:!border-rose-300 hover:!text-rose-800 dark:!bg-rose-950 dark:!border-rose-800 dark:!text-rose-100 dark:hover:!bg-rose-900 dark:hover:!border-rose-700 dark:hover:!text-white' : 'bg-rose-600 border-rose-600 text-white hover:bg-rose-500 hover:border-rose-500 dark:bg-rose-500 dark:border-rose-500 dark:text-white dark:hover:bg-rose-600 dark:hover:border-rose-600'),
            'indigo' => $outline ? 'bg-transparent border-indigo-200 text-indigo-700 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-800 dark:bg-transparent dark:border-indigo-700 dark:text-indigo-200 dark:hover:bg-indigo-950 dark:hover:border-indigo-600 dark:hover:text-indigo-100' : ($accent ? '!bg-indigo-50 !border-indigo-200 !text-indigo-700 hover:!bg-indigo-100 hover:!border-indigo-300 hover:!text-indigo-800 dark:!bg-indigo-950 dark:!border-indigo-800 dark:!text-indigo-100 dark:hover:!bg-indigo-900 dark:hover:!border-indigo-700 dark:hover:!text-white' : 'bg-indigo-600 border-indigo-600 text-white hover:bg-indigo-500 hover:border-indigo-500 dark:bg-indigo-500 dark:border-indigo-500 dark:text-white dark:hover:bg-indigo-600 dark:hover:border-indigo-600'),
            'teal' => $outline ? 'bg-transparent border-teal-200 text-teal-700 hover:bg-teal-50 hover:border-teal-300 hover:text-teal-800 dark:bg-transparent dark:border-teal-700 dark:text-teal-200 dark:hover:bg-teal-950 dark:hover:border-teal-600 dark:hover:text-teal-100' : ($accent ? '!bg-teal-50 !border-teal-200 !text-teal-700 hover:!bg-teal-100 hover:!border-teal-300 hover:!text-teal-800 dark:!bg-teal-950 dark:!border-teal-800 dark:!text-teal-100 dark:hover:!bg-teal-900 dark:hover:!border-teal-700 dark:hover:!text-white' : 'bg-teal-600 border-teal-600 text-white hover:bg-teal-500 hover:border-teal-500 dark:bg-teal-500 dark:border-teal-500 dark:text-white dark:hover:bg-teal-600 dark:hover:border-teal-600'),
            'cyan' => $outline ? 'bg-transparent border-cyan-200 text-cyan-700 hover:bg-cyan-50 hover:border-cyan-300 hover:text-cyan-800 dark:bg-transparent dark:border-cyan-700 dark:text-cyan-200 dark:hover:bg-cyan-950 dark:hover:border-cyan-600 dark:hover:text-cyan-100' : ($accent ? '!bg-cyan-50 !border-cyan-200 !text-cyan-700 hover:!bg-cyan-100 hover:!border-cyan-300 hover:!text-cyan-800 dark:!bg-cyan-950 dark:!border-cyan-800 dark:!text-cyan-100 dark:hover:!bg-cyan-900 dark:hover:!border-cyan-700 dark:hover:!text-white' : 'bg-cyan-600 border-cyan-600 text-white hover:bg-cyan-500 hover:border-cyan-500 dark:bg-cyan-500 dark:border-cyan-500 dark:text-white dark:hover:bg-cyan-600 dark:hover:border-cyan-600'),
            'emerald' => $outline ? 'bg-transparent border-emerald-200 text-emerald-700 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-800 dark:bg-transparent dark:border-emerald-700 dark:text-emerald-200 dark:hover:bg-emerald-950 dark:hover:border-emerald-600 dark:hover:text-emerald-100' : ($accent ? '!bg-emerald-50 !border-emerald-200 !text-emerald-700 hover:!bg-emerald-100 hover:!border-emerald-300 hover:!text-emerald-800 dark:!bg-emerald-950 dark:!border-emerald-800 dark:!text-emerald-100 dark:hover:!bg-emerald-900 dark:hover:!border-emerald-700 dark:hover:!text-white' : 'bg-emerald-600 border-emerald-600 text-white hover:bg-emerald-500 hover:border-emerald-500 dark:bg-emerald-500 dark:border-emerald-500 dark:text-white dark:hover:bg-emerald-600 dark:hover:border-emerald-600'),
            default => '',
        };
    }

    private function resolveStyle(?array $color, ?string $paletteName, bool $outline): string
    {
        if ($paletteName !== null && trim($paletteName) !== '') {
            return '';
        }

        if ($color === null) {
            return '';
        }

        $variables = $outline
            ? [
                '--cp-action-bg: transparent',
                '--cp-action-bg-hover: ' . ($this->paletteShade($color, 50) ?? 'transparent'),
                '--cp-action-border: ' . ($this->paletteShade($color, 200) ?? 'currentColor'),
                '--cp-action-border-hover: ' . ($this->paletteShade($color, 300) ?? 'currentColor'),
                '--cp-action-text: ' . ($this->paletteShade($color, 700) ?? 'currentColor'),
                '--cp-action-text-hover: ' . ($this->paletteShade($color, 800) ?? 'currentColor'),
                '--cp-action-dark-bg: transparent',
                '--cp-action-dark-bg-hover: ' . ($this->paletteShade($color, 950) ?? 'transparent'),
                '--cp-action-dark-border: ' . ($this->paletteShade($color, 700) ?? 'currentColor'),
                '--cp-action-dark-border-hover: ' . ($this->paletteShade($color, 600) ?? 'currentColor'),
                '--cp-action-dark-text: ' . ($this->paletteShade($color, 200) ?? '#e4e4e7'),
                '--cp-action-dark-text-hover: ' . ($this->paletteShade($color, 100) ?? '#f4f4f5'),
            ]
            : [
                '--cp-action-bg: ' . ($this->paletteShade($color, 500) ?? '#18181b'),
                '--cp-action-bg-hover: ' . ($this->paletteShade($color, 600) ?? '#27272a'),
                '--cp-action-border: ' . ($this->paletteShade($color, 500) ?? '#18181b'),
                '--cp-action-border-hover: ' . ($this->paletteShade($color, 600) ?? '#27272a'),
                '--cp-action-text: ' . $this->footerActionSolidTextColor($color),
                '--cp-action-text-hover: ' . $this->footerActionSolidTextColor($color),
                '--cp-action-dark-bg: ' . ($this->paletteShade($color, 600) ?? '#52525b'),
                '--cp-action-dark-bg-hover: ' . ($this->paletteShade($color, 700) ?? '#3f3f46'),
                '--cp-action-dark-border: ' . ($this->paletteShade($color, 600) ?? '#52525b'),
                '--cp-action-dark-border-hover: ' . ($this->paletteShade($color, 700) ?? '#3f3f46'),
                '--cp-action-dark-text: ' . $this->footerActionSolidTextColor($color),
                '--cp-action-dark-text-hover: ' . $this->footerActionSolidTextColor($color),
            ];

        return implode('; ', array_filter($variables));
    }

    private function evaluate(mixed $value): mixed
    {
        if (! $value instanceof Closure) {
            return $value;
        }

        return app()->call($value, [
            'action' => $this,
        ]);
    }
}
