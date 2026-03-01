<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\FunctionTypeArgumentNode;
use FFI\Generator\Node\Type\ConstTypeNode;
use FFI\Generator\Node\Type\FunctionTypeNode;
use FFI\Generator\Node\Type\RestrictTypeNode;
use FFI\Generator\Node\Type\VolatileTypeNode;

/**
 * @template-extends TypeBuilder<FunctionTypeNode>
 */
final readonly class FunctionTypeNodeTypeBuilder extends TypeBuilder
{
    public function supports(string $name): bool
    {
        return $name === 'FunctionType';
    }

    public function create(\DOMElement $el): FunctionTypeNode
    {
        return new \ReflectionClass(FunctionTypeNode::class)
            ->newLazyProxy(function () use ($el): FunctionTypeNode {
                $result = new FunctionTypeNode(
                    type: $this->getTypeReference($el, 'returns'),
                );

                $this->fillFunctionTypeArguments($result, $el);

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
            });
    }

    private function fillFunctionTypeArguments(FunctionTypeNode $function, \DOMElement $el): void
    {
        /** @var \DOMElement $member */
        foreach ($el->childNodes as $member) {
            switch ($member->nodeName) {
                case 'Argument':
                    $function->arguments[] = $this->createFunctionTypeArgumentNode($member);
                    break;

                case 'Ellipsis':
                    $this->markLastAsVariadic($function);
                    break;
            }
        }
    }

    private function markLastAsVariadic(FunctionTypeNode $function): void
    {
        $argumentId = \array_key_last($function->arguments);

        if ($argumentId === null) {
            throw new \LogicException('Cannot make variadic argument of empty function');
        }

        $function->arguments[$argumentId]
            ->isVariadic = true;
    }

    private function createFunctionTypeArgumentNode(\DOMElement $el): FunctionTypeArgumentNode
    {
        return new FunctionTypeArgumentNode(
            type: $this->getTypeReference($el, 'type'),
        );
    }
}
