<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

interface OptionalNamedTypeInterface extends TypeInterface
{
    /**
     * Gets a type name if it is present
     *
     * @var non-empty-string|null
     */
    public ?string $name {
        get;
    }
}
