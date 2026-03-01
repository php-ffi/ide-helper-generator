<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

/**
 * @template T of TypeInterface
 *
 * @template-implements TypeModifierInterface<T>
 */
final class ConstTypeNode extends TypeNode implements TypeModifierInterface
{
    public function __construct(
        /**
         * @var T
         */
        public readonly TypeInterface $type,
    ) {}
}
