<?php

declare(strict_types=1);

namespace FFI\AutocompleteGenerator\Node\Type;

use FFI\AutocompleteGenerator\Node\EnumValueNode;
use FFI\AutocompleteGenerator\Node\LocationNode;
use FFI\AutocompleteGenerator\Node\OptionalNamedNode;
use FFI\AutocompleteGenerator\Node\Visitable;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class EnumTypeNode extends OptionalNamedNode implements OptionalNamedTypeInterface
{
    #[Visitable]
    public LocationNode $location;

    /**
     * @var list<EnumValueNode>
     */
    public array $values = [];

    /**
     * @param non-empty-string|null $name
     * @param int<1, max> $size
     * @param int<1, max> $align
     */
    public function __construct(
        ?string $name,
        public readonly int $size = 32,
        public readonly int $align = 32,
    ) {
        parent::__construct($name);
    }
}
