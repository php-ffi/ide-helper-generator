<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\FunctionNode;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\Type\TypeInterface;
use FFI\Generator\Node\VariableNode;

/**
 * @template-extends TypeBuilder<NamespaceNode>
 */
final readonly class NamespaceNodeBuilder extends Builder
{
    private const string IS_GLOBAL_NAMESPACE = '::';

    public function supports(string $name): bool
    {
        return $name === 'Namespace';
    }

    public function create(\DOMElement $el): NamespaceNode
    {
        $name = $el->getAttribute('name');

        if ($name === '' || $name === self::IS_GLOBAL_NAMESPACE) {
            $name = null;
        }

        $result = new NamespaceNode($name);

        foreach ($this->document->getElementsByIdListString($el->getAttribute('members')) as $member) {
            $node = $this->context->create($member);

            if ($node instanceof TypeInterface) {
                $result->types[] = $node;
            } elseif ($node instanceof FunctionNode) {
                $result->functions[] = $node;
            } elseif ($node instanceof VariableNode) {
                $result->variables[] = $node;
            } else {
                throw new \LogicException('Unexpected node type in namespace: ' . $node::class);
            }
        }

        return $result;
    }
}
