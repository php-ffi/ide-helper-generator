<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

use FFI\Generator\Node\FunctionTypeArgumentNode;
use FFI\Generator\Node\Node;

/**
 * @template T of TypeInterface
 */
final class FunctionTypeNode extends Node implements TypeInterface
{
    /**
     * @var list<FunctionTypeArgumentNode>
     */
    public array $arguments = [];

    public function __construct(
        /**
         * @var T
         */
        public readonly TypeInterface $type,
    ) {}
}
