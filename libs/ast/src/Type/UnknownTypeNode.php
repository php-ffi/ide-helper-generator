<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

use FFI\Generator\Node\Node;

final class UnknownTypeNode extends Node implements TypeInterface
{
    public function __construct(
        public readonly string $typeName,
    ) {}
}
