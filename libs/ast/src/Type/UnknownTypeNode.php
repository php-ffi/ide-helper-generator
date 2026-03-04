<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

final class UnknownTypeNode extends TypeNode
{
    public function __construct(
        public readonly string $typeName,
    ) {
        parent::__construct();
    }
}
