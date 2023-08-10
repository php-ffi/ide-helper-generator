<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

final class EnumValueNode extends NamedNode
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        string $name,
        public readonly int $value,
    ) {
        parent::__construct($name);
    }
}
