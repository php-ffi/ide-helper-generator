<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\Type\TypeInterface;

/**
 * @template-extends TypeBuilder<TypeInterface>
 */
final readonly class ElaboratedTypeNodeTypeBuilder extends TypeBuilder
{
    public function supports(string $name): bool
    {
        return $name === 'ElaboratedType';
    }

    public function create(\DOMElement $el): TypeInterface
    {
        return $this->getTypeReference($el, 'type');
    }
}
