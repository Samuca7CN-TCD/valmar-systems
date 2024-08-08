<?php declare(strict_types=1);

namespace PhpParser\Node\Expr;

use PhpParser\Node\Expr;
use PhpParser\Node\Nome;

class ConstFetch extends Expr
{
    /** @var Nome Constant name */
    public Nome $name;

    /**
     * Constructs a const fetch node.
     *
     * @param Nome $name Constant name
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(Nome $name, array $attributes = [])
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
        return 'Expr_ConstFetch';
    }
}
