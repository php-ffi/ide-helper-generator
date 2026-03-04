<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

use FFI\Generator\Node\DefinitionInterface;
use FFI\Generator\Node\EnumValueNode;
use FFI\Generator\Node\Location;

final class EnumerationTypeNode extends TypeNode implements
    OptionalNamedTypeInterface,
    DefinitionInterface
{
    /**
     * @var list<EnumValueNode>
     */
    public array $values = [];

    public function __construct(
        /**
         * @var non-empty-string|null
         */
        public readonly ?string $name,
        public readonly Location $location,
        /**
         * @var int<0, max>
         */
        public readonly int $size = 32,
        /**
         * @var int<1, max>
         */
        public readonly int $align = 32,
    ) {
        parent::__construct();
    }
}
