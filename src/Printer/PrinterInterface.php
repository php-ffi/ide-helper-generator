<?php

declare(strict_types=1);

namespace FFI\Generator\Printer;

use FFI\Generator\GeneratedResult;
use FFI\Generator\Node\NamespaceNode;

/**
 * @template-covariant TResult of GeneratedResult = GeneratorResult
 */
interface PrinterInterface
{
    public const int DEFAULT_PHP_VERSION = \PHP_VERSION_ID;

    /**
     * @param iterable<mixed, NamespaceNode> $nodes
     *
     * @return TResult
     */
    public function print(iterable $nodes, int $phpVersion = self::DEFAULT_PHP_VERSION): GeneratedResult;
}
