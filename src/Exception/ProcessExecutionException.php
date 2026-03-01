<?php

declare(strict_types=1);

namespace FFI\Generator\Exception;

final class ProcessExecutionException extends ProcessException
{
    public static function becauseProcessNotRunnable(string $error, int $code, ?\Throwable $previous = null): self
    {
        return new self($error, $code, $previous);
    }
}
