<?php declare(strict_types=1);

namespace PhpParser\Node;

use PhpParser\Node;
use PhpParser\NodeAbstract;

class Attribute extends NodeAbstract
{
    /** @var Nome Attribute name */
    public Nome $name;

    /** @var list<Arg> Attribute arguments */
    public array $args;

    /**
     * @param Node\Nome $name Attribute name
     * @param list<Arg> $args Attribute arguments
     * @param array<string, mixed> $attributes Additional node attributes
     */
    public function __construct(Nome $name, array $args = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->name = $name;
        $this->args = $args;
    }

    public function getSubNodeNames(): array
    {
        return ['name', 'args'];
    }

    public function getType(): string
    {
        return 'Attribute';
    }
}
