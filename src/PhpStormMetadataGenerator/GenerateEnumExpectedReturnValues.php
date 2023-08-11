<?php

declare(strict_types=1);

namespace FFI\Generator\PhpStormMetadataGenerator;

use FFI\Generator\NamingStrategyInterface;
use FFI\Generator\Node\Type\EnumTypeNode;
use FFI\Generator\Node\FunctionNode;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\Type\TypeDefinitionNode;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;

/**
 * Generates "expectedReturnValues(\ClassName::func(), ...args...)"
 * Generates "expectedReturnValues(\ClassName::func(), argumentsSet('<NAME>'))"
 */
final class GenerateEnumExpectedReturnValues extends Visitor
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
            || !$node->returns instanceof TypeDefinitionNode
            || !$node->returns->type instanceof EnumTypeNode
            || $node->location->matches($this->excludes)) {
            return;
        }

        $expectedReturnValues = new FuncCall(new Name('expectedReturnValues'));

        $expectedReturnValues->args[] = new Arg(new StaticCall(
            class: new Name\FullyQualified($this->naming->getEntrypoint()),
            name: $node->name,
        ));

        $expectedReturnValues->args[] = new Arg(new FuncCall(
            name: new Name('argumentsSet'),
            args: [new Arg(new String_(
                value: $this->argumentSetPrefix . \strtolower($node->returns->name)
            ))]
        ));

        yield new Expression($expectedReturnValues);
    }
}
