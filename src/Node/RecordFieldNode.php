<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

use FFI\Generator\Node\Type\TypeInterface;

/**
 * @template T of TypeInterface
 */
final class RecordFieldNode extends Node
{
    public function __construct(
        /**
         * @var non-empty-string|null
         */
        public readonly ?string $name,
        /**
         * @var T
         */
        public TypeInterface $type,
    ) {}
}
