<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\Type\UnionTypeNode;

/**
 * @template-extends RecordTypeNodeTypeBuilder<UnionTypeNode>
 */
final readonly class UnionTypeNodeBuilder extends RecordTypeNodeTypeBuilder
{
    public function supports(string $name): bool
    {
        return $name === 'Union';
    }

    public function create(\DOMElement $el): UnionTypeNode
    {
        return new \ReflectionClass(UnionTypeNode::class)
            ->newLazyProxy(function () use ($el): UnionTypeNode {
                return $this->createUnionTypeNode($el);
            });
    }

    private function createUnionTypeNode(\DOMElement $el): UnionTypeNode
    {
        $name = $el->getAttribute('name');

        $union = new UnionTypeNode(
            name: $name === '' ? null : $name,
            location: $this->getLocationReference($el, 'location'),
            isIncomplete: $el->getAttribute('incomplete') === '1',
        );

        $this->fillRecordFields($union, $el);

        return $union;
    }
}
