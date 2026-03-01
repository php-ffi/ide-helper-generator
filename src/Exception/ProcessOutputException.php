<?php

declare(strict_types=1);

namespace FFI\Generator\Exception;

final class ProcessOutputException extends ProcessException
{
    public static function becauseOutputIsInvalid(string $expected, string $output, ?\Throwable $previous = null): self
    {
        $message = \sprintf('Expected output to contain "%s", but got "%s"', $expected, $output);

        return new self($message, 0, $previous);
    }
}
