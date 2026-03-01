<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\VariableNode;

/**
 * @template-extends TypeBuilder<VariableNode>
 */
final readonly class VariableNodeBuilder extends Builder
{
    public function supports(string $name): bool
    {
        return $name === 'Variable';
    }

    public function create(\DOMElement $el): VariableNode
    {
        return new \ReflectionClass(VariableNode::class)
            ->newLazyProxy(function () use ($el): VariableNode {
                $name = $el->getAttribute('name');

                if ($name === '') {
                    throw new \InvalidArgumentException('Variable name cannot be empty');
                }

                return new VariableNode(
                    name: $name,
                    location: $this->getLocationReference($el, 'location'),
                    type: $this->getTypeReference($el, 'type'),
                );
            });
    }
}
