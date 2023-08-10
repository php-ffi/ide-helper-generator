<?php

declare(strict_types=1);

namespace FFI\AutocompleteGenerator\PhpStormMetadataGenerator;

use FFI\AutocompleteGenerator\NamingStrategyInterface;
use FFI\AutocompleteGenerator\Node\Type\EnumTypeNode;
use FFI\AutocompleteGenerator\Node\FunctionNode;
use FFI\AutocompleteGenerator\Node\NamespaceNode;
use FFI\AutocompleteGenerator\Node\Type\TypeDefinitionNode;
use PhpParser\Comment;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;

/**
 * Generates "registerArgumentsSet('<name>', ...args...)"
 */
final class GenerateEnumArgumentsSet extends Visitor
{
    public function __construct(
        private readonly NamingStrategyInterface $naming,
        private readonly string $argumentSetPrefix,
    ) {
    }

    public function enter(NamespaceNode $ctx, TypeDefinitionNode|FunctionNode $node): iterable
    {
        if (!$node instanceof TypeDefinitionNode || !$node->type instanceof EnumTypeNode) {
            return;
        }

        $registerArgumentsSet = new FuncCall(
            name: new Name('registerArgumentsSet'),
            args: [new Arg(
                value: new String_(
                    value: $this->argumentSetPrefix . \strtolower($node->name)
                ),
                attributes: [
                    'comments' => [
                        new Comment('// List of "' . $node->name . '" enum cases')
                    ]
                ]
            )]
        );

        $phpEnumName = $this->naming->getName($node->getName(), $node->type);

        foreach ($node->type->values as $value) {
            $phpEnumValueName = $this->naming->getName($value->name, $value);

            $registerArgumentsSet->args[] = new Arg(new ClassConstFetch(
                class: new Name\FullyQualified($phpEnumName),
                name: $phpEnumValueName,
            ));
        }

        yield new Expression($registerArgumentsSet);
    }
}
