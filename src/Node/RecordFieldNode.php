<?php

declare(strict_types=1);

namespace FFI\AutocompleteGenerator\Node;

use FFI\AutocompleteGenerator\Node\Type\TypeInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class RecordFieldNode extends OptionalNamedNode
{
    #[Visitable]
    public TypeInterface $type;
}
