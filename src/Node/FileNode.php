<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

final class FileNode extends OptionalNamedNode
{
    public const DEFAULT_GLOBAL_NAME = '<builtin>';

    /**
     * @psalm-taint-sink file $name
     * @param non-empty-string|null $name
     */
    public function __construct(?string $name = null)
    {
        if ($name === self::DEFAULT_GLOBAL_NAME) {
            $name = null;
        }

        parent::__construct($name);
    }
}
