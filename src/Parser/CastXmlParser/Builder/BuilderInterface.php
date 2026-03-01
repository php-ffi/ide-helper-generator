<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\NodeInterface;

/**
 * @template TNode of NodeInterface
 */
interface BuilderInterface
{
    /**
     * @param non-empty-string $name
     */
    public function supports(string $name): bool;

    /**
     * @return TNode
     */
    public function create(\DOMElement $el): NodeInterface;
}
