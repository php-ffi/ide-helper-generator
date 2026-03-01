<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\Type\PointerTypeNode;

/**
 * @template-extends TypeBuilder<PointerTypeNode>
 */
final readonly class PointerTypeNodeTypeBuilder extends TypeBuilder
{
    public function supports(string $name): bool
    {
        return $name === 'PointerType';
    }

    public function create(\DOMElement $el): PointerTypeNode
    {
        return new \ReflectionClass(PointerTypeNode::class)
            ->newLazyProxy(function () use ($el): PointerTypeNode {
                return new PointerTypeNode(
                    type: $this->getTypeReference($el, 'type'),
                );
            });
    }
}
