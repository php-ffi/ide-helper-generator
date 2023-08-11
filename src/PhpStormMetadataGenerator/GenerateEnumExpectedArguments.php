<?php

declare(strict_types=1);

namespace FFI\Generator\PhpStormMetadataGenerator;

use FFI\Generator\NamingStrategyInterface;
use FFI\Generator\Node\Type\EnumTypeNode;
use FFI\Generator\Node\FunctionNode;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\Type\TypeDefinitionNode;
use PhpParser\Node\Stmt\Expression;

/**
 * Generates "expectedArguments(\ClassName::func(), <NUM>, argumentsSet('<NAME>'))"
 */
final class GenerateEnumExpectedArguments extends Visitor
{
    /**
     * @param list<non-empty-string> $excludes
     */
    public function __construct(
        private readonly NamingStrategyInterface $naming,
        private readonly string $argumentSetPrefix,
        private readonly array $excludes = [],
    ) {
    }

    public function enter(NamespaceNode $ctx, TypeDefinitionNode|FunctionNode $node): iterable
    {
        if (!$node instanceof FunctionNode
            || $node->name === null
            || $node->location->matches($this->excludes)) {
            return;
        }

        foreach ($node->arguments as $i => $argument) {
            if (!$argument->type instanceof TypeDefinitionNode
                || !$argument->type->type instanceof EnumTypeNode) {
                continue;
            }

            yield new Expression($this->expectedArguments(
                class: $this->naming->getEntrypoint(),
                method: $node->name,
                willExpects: $this->argumentsSet($this->argumentSetPrefix . \strtolower($argument->type->name)),
                argumentId: $i,
            ));
        }
    }
}
