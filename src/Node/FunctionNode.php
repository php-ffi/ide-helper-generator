<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

use FFI\Generator\Node\Type\TypeInterface;

/**
 * @template T of TypeInterface
 */
final class FunctionNode extends Node implements
    DefinitionInterface
{
    /**
     * @var list<FunctionArgumentNode>
     */
    public array $arguments = [];

    public function __construct(
        /**
         * @var non-empty-string|null
         */
        public readonly ?string $name,
        public readonly Location $location,
        /**
         * @var T
         */
        public readonly TypeInterface $type,
    ) {}
}
