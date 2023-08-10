<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

use FFI\Generator\Node\FunctionArgumentNode;
use FFI\Generator\Node\Node;
use FFI\Generator\Node\Visitable;

/**
 * @template TReturns of TypeInterface
 *
 * @psalm-suppress MissingConstructor
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class FunctionTypeNode extends Node implements TypeInterface
{
    /**
     * @var TReturns
     */
    #[Visitable]
    public TypeInterface $returns;

    /**
     * @var list<FunctionArgumentNode>
     */
    #[Visitable]
    public array $arguments = [];
}
