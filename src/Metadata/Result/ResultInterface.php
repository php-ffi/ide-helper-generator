<?php

declare(strict_types=1);

namespace FFI\Generator\Metadata\Result;

interface ResultInterface extends \Stringable
{
    /**
     * @psalm-taint-sink file $filename
     * @param non-empty-string $filename
     */
    public function save(string $filename): self;

    public function getContents(): string;
}
