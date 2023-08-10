<?php

declare(strict_types=1);

namespace FFI\Generator\Metadata\Result;

use FFI\Generator\Exception\ProcessException;

final class SourceResult extends Result
{
    protected ?string $contents = null;

    public function __construct(
        protected readonly \SplFileInfo $file,
        protected readonly bool $disposable = false,
    ) {
    }

    public static function createFromFilename(string $filename, bool $disposable = false): self
    {
        return new self(new \SplFileInfo($filename), $disposable);
    }

    public function save(string $filename): SourceResult
    {
        $this->createDirectoryForFile($filename);

        \error_clear_last();

        $status = @\copy($this->file->getPathname(), $filename);

        if (!$status) {
            throw new ProcessException(\error_get_last()['message'] ?? 'Can not save output file');
        }

        return SourceResult::createFromFilename($filename);
    }

    public function getContents(): string
    {
        return $this->contents ??= \file_get_contents($this->file->getPathname());
    }

    public function __destruct()
    {
        if ($this->disposable) {
            @\unlink($this->file->getPathname());
        }
    }
}
