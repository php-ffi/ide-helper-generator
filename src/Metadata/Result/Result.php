<?php

declare(strict_types=1);

namespace FFI\Generator\Metadata\Result;

use FFI\Generator\Exception\ProcessException;

abstract class Result implements ResultInterface
{
    protected function createDirectoryForFile(string $filename): void
    {
        $directory = \dirname($filename);

        if (!@\mkdir($directory, recursive: true) && !\is_dir($directory)) {
            throw new ProcessException(\sprintf('Directory [%s] is not available for writing', $directory));
        }
    }

    public function save(string $filename): SourceResult
    {
        $this->createDirectoryForFile($filename);

        \file_put_contents($filename, $this->getContents(), \LOCK_EX);

        return SourceResult::createFromFilename($filename);
    }

    public function __toString(): string
    {
        return $this->getContents();
    }
}
