<?php

declare(strict_types=1);

namespace FFI\Generator\PhpStormMetadataGenerator;

use FFI\Generator\NamingStrategyInterface;
use FFI\Generator\Node\Type\EnumTypeNode;
use FFI\Generator\Node\FunctionNode;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\Type\TypeDefinitionNode;
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
