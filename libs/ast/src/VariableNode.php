<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

use FFI\Generator\Node\Type\TypeInterface;

/**
 * @template T of TypeInterface
 */
final class VariableNode extends Node implements DefinitionInterface
{
    public function __construct(
        /**
         * @var T
         */
        public readonly string $name,
        public readonly Location $location,
        /**
         * @var T
         */
        public readonly TypeInterface $type,
    ) {}
}
