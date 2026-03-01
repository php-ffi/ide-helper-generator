<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\EnumValueNode;
use FFI\Generator\Node\Type\EnumerationTypeNode;

/**
 * @template-extends TypeBuilder<EnumerationTypeNode>
 */
final readonly class EnumerationTypeNodeTypeBuilder extends TypeBuilder
{
    public function supports(string $name): bool
    {
        return $name === 'Enumeration';
    }

    public function create(\DOMElement $el): EnumerationTypeNode
    {
        $name = $el->getAttribute('name');

        $enum = new EnumerationTypeNode(
            name: $name === '' ? null : $name,
            location: $this->getLocationReference($el, 'location'),
            size: $this->getSizeAttribute($el),
            align: $this->getAlignAttribute($el),
        );

        $this->fillEnumValues($enum, $el);

        return $enum;
    }

    private function fillEnumValues(EnumerationTypeNode $enum, \DOMElement $el): void
    {
        /** @var \DOMElement $value */
        foreach ($el->childNodes as $value) {
            if ($value->nodeName !== 'EnumValue') {
                continue;
            }

            $enum->values[] = $this->createEnumValueNode($value);
        }
    }

    /**
     * TODO Add support of:
     *   <xs:attribute name="attributes" type="attributes" use="optional" />
     *   <xs:attribute name="deprecation" type="name" use="optional"/>
     *   <xs:attribute name="annotation" type="name" use="optional"/>
     */
    private function createEnumValueNode(\DOMElement $el): EnumValueNode
    {
        $name = $el->getAttribute('name');

        if ($name === '') {
            throw new \InvalidArgumentException('Enum value name cannot be empty');
        }

        $value = (int) $el->getAttribute('init');

        return new EnumValueNode($name, $value);
    }
}
