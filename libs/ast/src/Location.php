<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

final class Location
{
    public const string DEFAULT_BUILTIN_FILE = '<builtin>';

    public const int MIN_LINE = 1;

    public bool $isBuiltin {
        get => $this->isBuiltin ??= $this->file === self::DEFAULT_BUILTIN_FILE;
    }

    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $file = self::DEFAULT_BUILTIN_FILE,
        /**
         * @var int<1, max>
         */
        public readonly int $line = self::MIN_LINE,
    ) {}

    /**
     * @param list<non-empty-string> $locations
     */
    public function in(array $locations): bool
    {
        if ($locations === []) {
            return true;
        }

        $pathname = \str_replace('\\', '/', $this->file);

        foreach ($locations as $directory) {
            if (\str_starts_with($pathname, \str_replace('\\', '/', $directory))) {
                return true;
            }
        }

        return false;
    }
}
