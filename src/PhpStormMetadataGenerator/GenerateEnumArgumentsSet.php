<?php

declare(strict_types=1);

namespace FFI\Generator\PhpStormMetadataGenerator;

use FFI\Generator\NamingStrategyInterface;
use FFI\Generator\Node\Type\EnumTypeNode;
use FFI\Generator\Node\FunctionNode;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\Type\TypeDefinitionNode;
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
        if (!$node instanceof TypeDefinitionNode
            || !$node->type instanceof EnumTypeNode
            || $node->location->matches($this->excludes)) {
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
