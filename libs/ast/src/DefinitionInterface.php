<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

interface DefinitionInterface
{
    public Location $location {
        get;
    }
}
