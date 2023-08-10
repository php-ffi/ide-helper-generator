<?php

declare(strict_types=1);

namespace FFI\AutocompleteGenerator\Node\Type;

use FFI\AutocompleteGenerator\Node\FunctionArgumentNode;
use FFI\AutocompleteGenerator\Node\Node;
use FFI\AutocompleteGenerator\Node\Visitable;

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
