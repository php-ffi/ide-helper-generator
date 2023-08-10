<?php

declare(strict_types=1);

namespace FFI\Generator;

use FFI\Generator\Node\NamespaceNode;

interface GeneratorInterface
{
    /**
     * @param iterable<array-key, NamespaceNode> $namespaces
     */
    public function generate(iterable $namespaces): ResultInterface;
}
