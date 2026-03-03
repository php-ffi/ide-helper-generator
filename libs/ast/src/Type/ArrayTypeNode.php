<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

/**
 * @template T of TypeInterface
 *
 * @template-implements GenericTypeInterface<T>
 */
final class ArrayTypeNode extends TypeNode implements GenericTypeInterface
{
    public function __construct(
        /**
         * @var T
         */
        public readonly TypeInterface $type,
        /**
         * @var int<0, max>
         */
        public readonly int $min,
        /**
         * @var int<0, max>|null
         */
        public readonly ?int $max = null,
    ) {}
}
