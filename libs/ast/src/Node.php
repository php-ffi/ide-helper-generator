<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

abstract class Node implements NodeInterface
{
    /**
     * @var array<non-empty-string, mixed>
     */
    public array $attributes = [];
}
