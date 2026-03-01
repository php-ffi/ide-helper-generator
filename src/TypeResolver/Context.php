<?php

declare(strict_types=1);

namespace FFI\Generator\TypeResolver;

final readonly class Context
{
    public function __construct(
        public Direction $direction,
        public TypeResolverInterface $types,
    ) {}
}
