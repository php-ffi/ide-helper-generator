<?php

declare(strict_types=1);

namespace FFI\AutocompleteGenerator\Metadata;

use FFI\AutocompleteGenerator\Node\NamespaceNode;

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
