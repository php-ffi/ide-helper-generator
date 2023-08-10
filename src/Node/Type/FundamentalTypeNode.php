<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

use FFI\Generator\Node\NamedNode;

final class FundamentalTypeNode extends NamedNode implements NamedTypeInterface
{
    /**
     * @param non-empty-string $name
     * @param int<1, max> $size
     * @param int<1, max> $align
     */
    public function __construct(
        string $name,
        public readonly int $size,
        public readonly int $align,
    ) {
        parent::__construct($name);
    }
}
