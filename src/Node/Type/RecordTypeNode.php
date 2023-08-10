<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

use FFI\Generator\Node\LocationNode;
use FFI\Generator\Node\OptionalNamedNode;
use FFI\Generator\Node\RecordFieldNode;
use FFI\Generator\Node\Visitable;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
abstract class RecordTypeNode extends OptionalNamedNode implements OptionalNamedTypeInterface
{
    #[Visitable]
    public LocationNode $location;

    /**
     * @var list<RecordFieldNode>
     */
    #[Visitable]
    public array $fields = [];
}
