<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

use FFI\Generator\Node\Type\TypeInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class FunctionNode extends OptionalNamedNode
{
    #[Visitable]
    public TypeInterface $returns;

    #[Visitable]
    public LocationNode $location;

    /**
     * @var list<FunctionArgumentNode>
     */
    #[Visitable]
    public array $arguments = [];
}
