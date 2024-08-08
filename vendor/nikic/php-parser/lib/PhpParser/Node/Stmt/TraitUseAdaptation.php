<?php declare(strict_types=1);

namespace PhpParser\Node\Stmt;

use PhpParser\Node;

abstract class TraitUseAdaptation extends Node\Stmt
{
    /** @var Node\Nome|null Trait name */
    public ?Node\Nome $trait;
    /** @var Node\Identifier Method name */
    public Node\Identifier $method;
}
