<?php

declare(strict_types=1);

namespace FFI\AutocompleteGenerator\Node;

final class LocationNode extends Node
{
    /**
     * @param int<0, max> $line
     */
    public function __construct(
        #[Visitable]
        public readonly FileNode $file,
        public readonly int $line = 0,
    ) {}

    /**
     * @param list<non-empty-string> $excludes
     */
    public function matches(array $excludes): bool
    {
        // Ignore builtin files
        if ($this->file->name === null) {
            return false;
        }

        $pathname = \str_replace('\\', '/', $this->file->name);

        foreach ($excludes as $ignored) {
            if (\str_starts_with($pathname, \str_replace('\\', '/', $ignored))) {
                return false;
            }
        }

        return true;
    }
}