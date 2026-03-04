<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

use FFI\Generator\Node\Type\TypeInterface;

/**
 * @template T of TypeInterface
 */
class FunctionTypeArgumentNode extends Node
{
    public function __construct(
        /**
         * @var T
         */
        public readonly TypeInterface $type,
        public bool $isVariadic = false,
    ) {
        parent::__construct();
    }
}
