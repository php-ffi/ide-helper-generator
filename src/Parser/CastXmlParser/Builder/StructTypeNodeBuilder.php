<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\Type\StructTypeNode;

/**
 * @template-extends RecordTypeNodeTypeBuilder<StructTypeNode>
 */
final readonly class StructTypeNodeBuilder extends RecordTypeNodeTypeBuilder
{
    public function supports(string $name): bool
    {
        return $name === 'Struct';
    }

    public function create(\DOMElement $el): StructTypeNode
    {
        return new \ReflectionClass(StructTypeNode::class)
            ->newLazyProxy(function () use ($el): StructTypeNode {
                return $this->createStructTypeNode($el);
            });
    }

    private function createStructTypeNode(\DOMElement $el): StructTypeNode
    {
        $name = $el->getAttribute('name');

        $struct = new StructTypeNode(
            name: $name === '' ? null : $name,
            location: $this->getLocationReference($el, 'location'),
            isIncomplete: $el->getAttribute('incomplete') === '1',
        );

        $this->fillRecordFields($struct, $el);

        return $struct;
    }
}
