<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\Type\ConstTypeNode;
use FFI\Generator\Node\Type\RestrictTypeNode;
use FFI\Generator\Node\Type\TypeInterface;
use FFI\Generator\Node\Type\VolatileTypeNode;

/**
 * @template-extends TypeBuilder<TypeInterface>
 */
final readonly class CvQualifiedTypeNodeTypeBuilder extends TypeBuilder
{
    public function supports(string $name): bool
    {
        return $name === 'CvQualifiedType';
    }

    public function create(\DOMElement $el): TypeInterface
    {
        $result = $this->getTypeReference($el, 'type');

        if ($el->getAttribute('const') === '1') {
            $result = new ConstTypeNode($result);
        }

        if ($el->getAttribute('volatile') === '1') {
            $result = new VolatileTypeNode($result);
        }

        if ($el->getAttribute('restrict') === '1') {
            $result = new RestrictTypeNode($result);
        }

        return $result;
    }
}
