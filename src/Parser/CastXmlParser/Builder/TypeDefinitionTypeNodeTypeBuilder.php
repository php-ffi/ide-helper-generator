<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\Type\TypeDefinitionTypeNode;

/**
 * @template-extends TypeBuilder<TypeDefinitionTypeNode>
 */
final readonly class TypeDefinitionTypeNodeTypeBuilder extends TypeBuilder
{
    public function supports(string $name): bool
    {
        return $name === 'Typedef';
    }

    public function create(\DOMElement $el): TypeDefinitionTypeNode
    {
        return new \ReflectionClass(TypeDefinitionTypeNode::class)
            ->newLazyProxy(function () use ($el): TypeDefinitionTypeNode {
                return $this->createTypeDefinitionTypeNode($el);
            });
    }

    private function createTypeDefinitionTypeNode(\DOMElement $el): TypeDefinitionTypeNode
    {
        $name = $el->getAttribute('name');

        if ($name === '') {
            throw new \InvalidArgumentException('Type definition name cannot be empty');
        }

        return new TypeDefinitionTypeNode(
            name: $name,
            location: $this->getLocationReference($el, 'location'),
            type: $this->getTypeReference($el, 'type'),
        );
    }
}
