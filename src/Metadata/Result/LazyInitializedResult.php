<?php

declare(strict_types=1);

namespace FFI\Generator\Metadata\Result;

final class LazyInitializedResult extends Result
{
    protected ?string $contents = null;

    /**
     * @param \Closure():string $initializer
     */
    public function __construct(
        private readonly \Closure $initializer,
    ) {
    }

    public function getContents(): string
    {
        return $this->contents ??= ($this->initializer)();
    }
}
