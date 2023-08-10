<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

use FFI\Generator\Node\GenericTypeInterface;
use FFI\Generator\Node\Node;
use FFI\Generator\Node\Visitable;

/**
 * @template TType of TypeInterface
 *
 * @template-implements GenericTypeInterface<TType>
 */
abstract class GenericType extends Node implements GenericTypeInterface
{
    /**
     * @param TType $type
     */
    public function __construct(
        #[Visitable]
        public readonly TypeInterface $type,
    ) {
    }

    public function getOfType(): TypeInterface
    {
        return $this->type;
    }
}
