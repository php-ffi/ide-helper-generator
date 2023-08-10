<?php

declare(strict_types=1);

namespace FFI\Generator\Metadata;

use FFI\Generator\Metadata\Result\ResultInterface;

interface GeneratorInterface
{
    public function isAvailable(): bool;

    /**
     * @psalm-taint-sink file $filename
     * @param non-empty-string $filename
     * @param non-empty-string|null $cwd
     * @param list<non-empty-string> $includes
     */
    public function generate(string $filename, string $cwd = null, array $includes = []): ResultInterface;
}
