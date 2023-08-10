<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

use FFI\Generator\Node\EnumValueNode;
use FFI\Generator\Node\LocationNode;
use FFI\Generator\Node\OptionalNamedNode;
use FFI\Generator\Node\Visitable;

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
