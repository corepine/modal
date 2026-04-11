<?php

namespace Corepine\Modal\Actions;

use Closure;

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

    private array | string | Closure | null $color = null;

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
        $color = $this->evaluate($this->color);
        $outline = $this->outline instanceof Closure || is_bool($this->outline)
            ? $this->evaluate($this->outline)
            : null;
        $attributes = $this->evaluate($this->attributes);

        if (! is_array($attributes)) {
            $attributes = [];
        }

        if ($this->type === 'close') {
            return [
                'type' => 'close',
                'label' => $this->label ?? 'Close',
                'class' => $this->class,
                'disabled' => $disabled,
                'color' => $color,
                'outline' => is_bool($outline) ? $outline : null,
                'attributes' => $attributes,
                'count' => $this->count,
                'destroy' => $this->destroy,
                'closeAll' => $this->closeAll,
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
            'class' => $this->class,
            'disabled' => $disabled,
            'color' => $color,
            'outline' => is_bool($outline) ? $outline : null,
            'attributes' => $attributes,
            'buttonType' => $this->buttonType,
        ];
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
