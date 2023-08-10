<?php

declare(strict_types=1);

namespace FFI\Generator\Metadata;

use FFI\Generator\Node\NamespaceNode;

interface ParserInterface
{
    /**
     * @psalm-taint-sink file $filename
     * @param non-empty-string $filename
     *
     * @return iterable<array-key, NamespaceNode>
     */
    public function parse(string $filename): iterable;
}
