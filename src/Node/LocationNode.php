<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

final class LocationNode extends Node
{
    /**
     * @param int<0, max> $line
     */
    public function __construct(
        #[Visitable]
        public readonly FileNode $file,
        public readonly int $line = 0,
    ) {
    }

    /**
     * @param list<non-empty-string> $excludes
     */
    public function matches(array $excludes): bool
    {
        if ($excludes === []) {
            return true;
        }

        // Ignore builtin files
        if ($this->file->name === null) {
            return true;
        }

        $pathname = \str_replace('\\', '/', $this->file->name);

        foreach ($excludes as $directory) {
            if (\str_starts_with($pathname, \str_replace('\\', '/', $directory))) {
                return true;
            }
        }

        return false;
    }
}
