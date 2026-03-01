<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

final readonly class UnimplementedTypeNode implements NamedTypeInterface
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $name,
    ) {}
}
