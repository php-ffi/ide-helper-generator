<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\Type\ArrayTypeNode;

/**
 * @template-extends TypeBuilder<ArrayTypeNode>
 */
final readonly class ArrayTypeNodeTypeBuilder extends TypeBuilder
{
    public function supports(string $name): bool
    {
        return $name === 'ArrayType';
    }

    public function create(\DOMElement $el): ArrayTypeNode
    {
        return new \ReflectionClass(ArrayTypeNode::class)
            ->newLazyProxy(function () use ($el): ArrayTypeNode {
                return new ArrayTypeNode(
                    type: $this->getTypeReference($el, 'type'),
                    size: match ($max = $el->getAttribute('max')) {
                        '' => null,
                        '-1' => 0,
                        default => (int) $max
                    },
                );
            });
    }
}
