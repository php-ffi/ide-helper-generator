<?php

declare(strict_types=1);

namespace FFI\Generator\Parser;

use FFI\Generator\Node\NamespaceNode;

interface ParserInterface
{
    /**
     * @param non-empty-string $pathname
     *
     * @return iterable<array-key, NamespaceNode>
     */
    public function parse(string $pathname): iterable;
}
