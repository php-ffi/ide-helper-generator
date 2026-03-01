<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\Location;
use FFI\Generator\Node\NodeInterface;
use FFI\Generator\Node\Type\TypeInterface;
use FFI\Generator\Parser\CastXmlParser\CastXmlParserResult\CastXmlDocument;
use FFI\Generator\Parser\CastXmlParser\CastXmlParserResult\CastXmlFileStorage;

/**
 * @template T of NodeInterface
 *
 * @template-implements BuilderInterface<T>
 */
abstract readonly class Builder implements BuilderInterface
{
    public function __construct(
        /**
         * @var BuilderInterface<NodeInterface>
         */
        protected BuilderInterface $context,
        protected CastXmlFileStorage $files,
        protected CastXmlDocument $document,
    ) {}

    protected function getLocationReference(\DOMElement $ctx, string $attribute): Location
    {
        return $this->files->getLocation($ctx->getAttribute($attribute));
    }

    protected function getTypeReference(\DOMElement $ctx, string $attribute): TypeInterface
    {
        $type = $ctx->getAttribute($attribute);

        if ($type === '') {
            throw new \InvalidArgumentException('Type reference cannot be empty');
        }

        $result = $this->context->create($this->document->getElementById($type));

        if ($result instanceof TypeInterface) {
            return $result;
        }

        throw new \InvalidArgumentException('Reference is not a type');
    }
}
