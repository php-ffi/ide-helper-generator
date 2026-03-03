<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

/**
 * @template T of TypeInterface
 */
interface GenericTypeInterface extends TypeInterface
{
    /**
     * @var T
     */
    public TypeInterface $type {
        get;
    }
}
