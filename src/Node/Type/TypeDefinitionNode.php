<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

use FFI\Generator\Node\GenericTypeInterface;
use FFI\Generator\Node\LocationNode;
use FFI\Generator\Node\NamedNode;
use FFI\Generator\Node\Visitable;

/**
 * @template TType of TypeInterface
 *
 * @template-implements GenericTypeInterface<TType>
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class TypeDefinitionNode extends NamedNode implements NamedTypeInterface, GenericTypeInterface
{
    /**
     * @var TType
     */
    #[Visitable]
    public TypeInterface $type;

    #[Visitable]
    public LocationNode $location;

    public function getOfType(): TypeInterface
    {
        return $this->type;
    }
}
