<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\Type\FundamentalTypeNode;

/**
 * @template-extends TypeBuilder<FundamentalTypeNode>
 */
final readonly class FundamentalTypeNodeTypeBuilder extends TypeBuilder
{
    public function supports(string $name): bool
    {
        return $name === 'FundamentalType';
    }

    public function create(\DOMElement $el): FundamentalTypeNode
    {
        $name = $el->getAttribute('name');

        if ($name === '') {
            throw new \InvalidArgumentException('Fundamental type name cannot be empty');
        }

        return new FundamentalTypeNode(
            name: $name,
            size: $this->getSizeAttribute($el),
            align: $this->getAlignAttribute($el),
        );
    }
}
