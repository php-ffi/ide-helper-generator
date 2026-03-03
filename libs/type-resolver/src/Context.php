<?php

declare(strict_types=1);

namespace FFI\Generator\TypeResolver;

use FFI\Generator\TypeResolver\Factory\TypeFactoryInterface;

final readonly class Context
{
    public function __construct(
        public Direction $direction,
        public TypeResolverInterface $resolver,
        public TypeFactoryInterface $factory,
    ) {}
}
