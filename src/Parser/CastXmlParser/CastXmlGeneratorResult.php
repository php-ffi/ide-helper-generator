<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser;

final readonly class CastXmlGeneratorResult
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $pathname,
    ) {}

    public function __destruct()
    {
        //@\unlink($this->pathname);
    }
}
