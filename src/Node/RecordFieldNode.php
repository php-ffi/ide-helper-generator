<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

use FFI\Generator\Node\Type\TypeInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class RecordFieldNode extends OptionalNamedNode
{
    #[Visitable]
    public TypeInterface $type;
}
