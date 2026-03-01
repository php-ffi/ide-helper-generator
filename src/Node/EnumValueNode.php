<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

final class EnumValueNode extends Node
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $name,
        public readonly int $value,
    ) {}
}
