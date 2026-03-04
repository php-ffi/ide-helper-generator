<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

final class FundamentalTypeNode extends TypeNode implements
    NamedTypeInterface
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $name,
        /**
         * @var int<0, max>
         */
        public readonly int $size,
        /**
         * @var int<1, max>
         */
        public readonly int $align,
    ) {
        parent::__construct();
    }
}
