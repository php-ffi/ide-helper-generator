<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

use FFI\Generator\Node\Type\TypeInterface;

final class UnknownTypeNode extends Node implements TypeInterface
{
    public function __construct(
        public readonly string $typeName,
    ) {
    }
}
