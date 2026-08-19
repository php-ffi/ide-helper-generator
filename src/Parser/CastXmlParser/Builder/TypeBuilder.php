<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\Type\TypeInterface;

/**
 * @template T of TypeInterface
 *
 * @template-extends Builder<T>
 */
abstract readonly class TypeBuilder extends Builder
{
    /**
     * @return int<1, max>
     */
    protected function getSizeAttribute(\DOMElement $el): int
    {
        $size = (int) $el->getAttribute('size');

        if ($size < 0) {
            throw new \InvalidArgumentException(\sprintf('Size cannot be less than 0, but %s given', $size));
        }

        return $size;
    }

    /**
     * @return int<1, max>
     */
    protected function getAlignAttribute(\DOMElement $el): int
    {
        $align = (int) $el->getAttribute('align');

        if ($align < 1) {
            throw new \InvalidArgumentException('Align cannot be less than 1');
        }

        return $align;
    }
}
