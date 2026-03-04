<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

use FFI\Generator\Node\DefinitionInterface;
use FFI\Generator\Node\Location;

/**
 * @template T of TypeInterface
 *
 * @template-implements GenericTypeInterface<T>
 */
final class TypeDefinitionTypeNode extends TypeNode implements
    NamedTypeInterface,
    DefinitionInterface,
    GenericTypeInterface
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $name,
        public readonly Location $location,
        /**
         * @var T
         */
        public readonly TypeInterface $type,
    ) {
        parent::__construct();
    }
}
