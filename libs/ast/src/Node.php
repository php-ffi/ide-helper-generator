<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

use PhpParser\NodeAbstract;

abstract class Node extends NodeAbstract implements
    NodeInterface
{
    public function getType(): string
    {
        return \str_replace('\\', '_', static::class);
    }

    public function getSubNodeNames(): array
    {
        return [];
    }
}
