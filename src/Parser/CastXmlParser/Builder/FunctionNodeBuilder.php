<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\FunctionArgumentNode;
use FFI\Generator\Node\FunctionNode;

/**
 * @template-extends TypeBuilder<FunctionNode>
 */
final readonly class FunctionNodeBuilder extends Builder
{
    public function supports(string $name): bool
    {
        return $name === 'Function';
    }

    public function create(\DOMElement $el): FunctionNode
    {
        return new \ReflectionClass(FunctionNode::class)
            ->newLazyProxy(function () use ($el): FunctionNode {
                $name = $el->getAttribute('name');

                $result = new FunctionNode(
                    name: $name === '' ? null : $name,
                    location: $this->getLocationReference($el, 'location'),
                    type: $this->getTypeReference($el, 'returns'),
                );

                $this->fillFunctionArguments($result, $el);

                return $result;
            });
    }

    private function fillFunctionArguments(FunctionNode $function, \DOMElement $el): void
    {
        /** @var \DOMElement $member */
        foreach ($el->childNodes as $member) {
            switch ($member->nodeName) {
                case 'Argument':
                    $function->arguments[] = $this->createFunctionArgumentNode($member);
                    break;

                case 'Ellipsis':
                    $this->markLastAsVariadic($function);
                    break;
            }
        }
    }

    private function markLastAsVariadic(FunctionNode $function): void
    {
        $argumentId = \array_key_last($function->arguments);

        if ($argumentId === null) {
            throw new \LogicException('Cannot make variadic argument of empty function');
        }

        $function->arguments[$argumentId]
            ->isVariadic = true;
    }

    private function createFunctionArgumentNode(\DOMElement $el): FunctionArgumentNode
    {
        $attribute = $el->getAttribute('original_type')
            ? 'original_type'
            : 'type';

        return new FunctionArgumentNode(
            type: $this->getTypeReference($el, $attribute),
            location: $this->getLocationReference($el, 'location'),
        );
    }
}
