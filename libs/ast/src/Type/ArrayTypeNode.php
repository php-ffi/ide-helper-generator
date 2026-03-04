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
         * If size is {@see null}, then the array is unbounded.
         *
         * @var int<0, max>|null
         */
        public readonly ?int $size = null,
    ) {
        parent::__construct();
    }
}
