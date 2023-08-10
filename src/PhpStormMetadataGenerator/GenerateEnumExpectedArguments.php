<?php

declare(strict_types=1);

namespace FFI\AutocompleteGenerator\PhpStormMetadataGenerator;

use FFI\AutocompleteGenerator\NamingStrategyInterface;
use FFI\AutocompleteGenerator\Node\Type\EnumTypeNode;
use FFI\AutocompleteGenerator\Node\FunctionNode;
use FFI\AutocompleteGenerator\Node\NamespaceNode;
use FFI\AutocompleteGenerator\Node\Type\TypeDefinitionNode;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;

/**
 * Generates "expectedArguments(\ClassName::func(), <NUM>, argumentsSet('<NAME>'))"
 */
final class GenerateEnumExpectedArguments extends Visitor
{
    public function __construct(
        private readonly NamingStrategyInterface $naming,
        private readonly string $argumentSetPrefix,
    ) {
    }

    public function enter(NamespaceNode $ctx, TypeDefinitionNode|FunctionNode $node): iterable
    {
        if (!$node instanceof FunctionNode || $node->name === null) {
            return;
        }

        $expectedArguments = new FuncCall(new Name('expectedArguments'));

        $expectedArguments->args[] = new Arg(new StaticCall(
            class: new Name\FullyQualified($this->naming->getEntrypoint()),
            name: $node->name,
        ));

        foreach ($node->arguments as $i => $argument) {
            if (!$argument->type instanceof TypeDefinitionNode
                || !$argument->type->type instanceof EnumTypeNode) {
                continue;
            }

            $function = clone $expectedArguments;
            $function->args[] = new Arg(new LNumber($i));
            $function->args[] = new Arg(new FuncCall(
                name: new Name('argumentsSet'),
                args: [new Arg(new String_(
                    value: $this->argumentSetPrefix . \strtolower($argument->type->name)
                ))]
            ));

            yield new Expression($function);
        }
    }
}
