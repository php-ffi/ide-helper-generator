<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

use FFI\Generator\Node\Type\TypeInterface;

/**
 * @template TType of TypeInterface
 */
interface GenericTypeInterface extends TypeInterface
{
    /**
     * @return TType
     */
    public function getOfType(): TypeInterface;
}
