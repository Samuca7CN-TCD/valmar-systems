<?php declare(strict_types=1);

namespace PhpParser\Node;

use PhpParser\NodeAbstract;

class Const_ extends NodeAbstract
{
    /** @var Identifier Nome */
    public Identifier $name;
    /** @var Expr Value */
    public Expr $value;

    /** @var Nome|null Namespaced name (if using NameResolver) */
    public ?Nome $namespacedName;

    /**
     * Constructs a const node for use in class const and const statements.
     *
     * @param string|Identifier $name Nome
     * @param Expr $value Value
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct($name, Expr $value, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->name = \is_string($name) ? new Identifier($name) : $name;
        $this->value = $value;
    }

    public function getSubNodeNames(): array
    {
        return ['name', 'value'];
    }

    public function getType(): string
    {
        return 'Const';
    }
}
