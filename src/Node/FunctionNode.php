<?php

declare(strict_types=1);

namespace FFI\AutocompleteGenerator\Node;

use FFI\AutocompleteGenerator\Node\Type\TypeInterface;

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
