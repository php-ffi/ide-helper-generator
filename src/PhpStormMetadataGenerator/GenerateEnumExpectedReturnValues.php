<?php

declare(strict_types=1);

namespace FFI\AutocompleteGenerator\PhpStormMetadataGenerator;

use FFI\AutocompleteGenerator\NamingStrategyInterface;
use FFI\AutocompleteGenerator\Node\Type\EnumTypeNode;
use FFI\AutocompleteGenerator\Node\FunctionNode;
use FFI\AutocompleteGenerator\Node\NamespaceNode;
use FFI\AutocompleteGenerator\Node\Type\TypeDefinitionNode;
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
    public function __construct(
        private readonly NamingStrategyInterface $naming,
        private readonly string $argumentSetPrefix,
    ) {
    }

    public function enter(NamespaceNode $ctx, TypeDefinitionNode|FunctionNode $node): iterable
    {
        if (!$node instanceof FunctionNode
            || $node->name === null
            || !$node->returns instanceof TypeDefinitionNode
            || !$node->returns->type instanceof EnumTypeNode
        ) {
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
