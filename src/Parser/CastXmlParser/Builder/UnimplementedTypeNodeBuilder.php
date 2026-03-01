<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\Type\UnimplementedTypeNode;

/**
 * @template-extends TypeBuilder<UnimplementedTypeNode>
 */
final readonly class UnimplementedTypeNodeBuilder extends TypeBuilder
{
    public function supports(string $name): bool
    {
        return $name === 'Unimplemented';
    }

    public function create(\DOMElement $el): UnimplementedTypeNode
    {
        $name = $el->getAttribute('kind');

        if ($name === '') {
            $name = $el->getAttribute('type_class');
        }

        if ($name === '') {
            throw new \InvalidArgumentException('Unimplemented type node must have a name or class');
        }

        return new UnimplementedTypeNode(
            name: $name,
        );
    }
}
