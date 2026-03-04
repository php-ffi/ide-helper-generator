<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

use FFI\Generator\Node\DefinitionInterface;
use FFI\Generator\Node\Location;
use FFI\Generator\Node\RecordFieldNode;

abstract class RecordTypeNode extends TypeNode implements
    OptionalNamedTypeInterface,
    DefinitionInterface
{
    /**
     * @var list<RecordFieldNode>
     */
    public array $fields = [];

    public function __construct(
        /**
         * @var non-empty-string|null
         */
        public readonly ?string $name,
        public readonly Location $location,
        public readonly bool $isIncomplete = false,
    ) {
        parent::__construct();
    }
}
