<?php declare(strict_types=1);

namespace PhpParser\Node\Expr;

use PhpParser\Node\Expr;

class Variable extends Expr
{
    /** @var string|Expr Nome */
    public $name;

    /**
     * Constructs a variable node.
     *
     * @param string|Expr $name Nome
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct($name, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->name = $name;
    }

    public function getSubNodeNames(): array
    {
        return ['name'];
    }

    public function getType(): string
    {
        return 'Expr_Variable';
    }
}
