<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

use FFI\Generator\Node\Type\TypeInterface;

/**
 * @template T of TypeInterface
 *
 * @template-extends FunctionTypeArgumentNode<T>
 */
final class FunctionArgumentNode extends FunctionTypeArgumentNode implements
    DefinitionInterface
{
    /**
     * @param T $type
     */
    public function __construct(
        TypeInterface $type,
        public readonly Location $location,
        bool $isVariadic = false
    ) {
        parent::__construct($type, $isVariadic);
    }
}
