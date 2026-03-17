<?php

namespace Corepine\Modal\Actions;

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

    private int $count = 1;

    private bool $destroy = true;

    private bool $force = false;

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

    public function close(int $count = 1, bool $destroy = true, bool $force = false): self
    {
        $this->type = 'close';
        $this->count = max(1, $count);
        $this->destroy = $destroy;
        $this->force = $force;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        if ($this->type === 'close') {
            return [
                'type' => 'close',
                'label' => $this->label ?? 'Close',
                'class' => $this->class,
                'count' => $this->count,
                'destroy' => $this->destroy,
                'force' => $this->force,
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
            'buttonType' => $this->buttonType,
        ];
    }
}
