<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

use FFI\Generator\Node\Type\TypeInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class FunctionTypeArgumentNode extends OptionalNamedNode
{
    public bool $variadic = false;

    #[Visitable]
    public TypeInterface $type;
}
