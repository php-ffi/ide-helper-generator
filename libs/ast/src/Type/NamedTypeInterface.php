<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

interface NamedTypeInterface extends OptionalNamedTypeInterface
{
    /**
     * Gets a type name
     *
     * @var non-empty-string
     */
    public string $name {
        get;
    }
}
