<?php

declare(strict_types=1);

namespace FFI\Generator\PhpStormMetadataGenerator;

use FFI\Generator\Node\FunctionNode;
use FFI\Generator\Node\GenericTypeInterface;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\Type\RecordTypeNode;
use FFI\Generator\Node\Type\TypeDefinitionNode;
use FFI\Generator\Node\Type\TypeInterface;
use PhpParser\Comment;
use PhpParser\Comment\Doc;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;

abstract class Visitor
{
    /**
     * @param iterable<TypeDefinitionNode|FunctionNode> $nodes
     * @return iterable<Stmt>
     */
    public function before(NamespaceNode $ctx, iterable $nodes): iterable
    {
        return [];
    }

    /**
     * @param iterable<TypeDefinitionNode|FunctionNode> $nodes
     * @return iterable<Stmt>
     */
    public function after(NamespaceNode $ctx, iterable $nodes): iterable
    {
        return [];
    }

    /**
     * @return iterable<Stmt>
     */
    public function enter(NamespaceNode $ctx, TypeDefinitionNode|FunctionNode $node): iterable
    {
        return [];
    }

    /**
     * @param non-empty-string $class
     * @param non-empty-string $method
     * @param int<0, max> $argumentId
     */
    protected function override(string $class, string $method, FuncCall $into, int $argumentId = 0): FuncCall
    {
        return new FuncCall(
            name: new Name('override'),
            args: [
                new Arg(new StaticCall(
                    class: new Name\FullyQualified($class),
                    name: $method,
                    args: [new Arg(new LNumber($argumentId))]
                )),
                new Arg($into)
            ]
        );
    }

    /**
     * @param iterable<array-key, ArrayItem> $items
     * @return FuncCall
     */
    protected function map(iterable $items): FuncCall
    {
        return new FuncCall(
            name: new Name('map'),
            args: [new Arg(new Array_(
                items: [...$items],
                attributes: ['kind' => Array_::KIND_SHORT],
            ))]
        );
    }

    /**
     * @param non-empty-string $name
     */
    protected function argumentsSet(string $name): FuncCall
    {
        return new FuncCall(
            name: new Name('argumentsSet'),
            args: [new Arg(new String_($name))]
        );
    }

    /**
     * @param non-empty-string $class
     * @param non-empty-string $method
     * @param int<0, max> $argumentId
     */
    protected function expectedArguments(string $class, string $method, Expr $willExpects, int $argumentId = 0): FuncCall
    {
        return new FuncCall(
            name: new Name('expectedArguments'),
            args: [
                new Arg(new StaticCall(
                    class: new Name\FullyQualified($class),
                    name: $method,
                )),
                new Arg(new LNumber($argumentId)),
                new Arg($willExpects)
            ]
        );
    }

    /**
     * @param non-empty-string $name
     */
    protected function registerArgumentsSet(string $name, string $comment = null): FuncCall
    {
        return new FuncCall(
            name: new Name('registerArgumentsSet'),
            args: [$this->argNode($name, $comment)],
        );
    }

    /**
     * @param non-empty-string $name
     */
    protected function argNode(string $name, string $comment = null): Arg
    {
        $argument = new Arg(new String_($name));

        if ($comment) {
            $argument->setDocComment(new Doc('// ' . $name));
        }

        return $argument;
    }

    /**
     * @param non-empty-string $value
     */
    protected function arrayItemNode(?string $key, string $value, string $comment = null): ArrayItem
    {
        $item = new ArrayItem(new String_($value), $key !== null ? new String_($key) : null);

        if ($comment) {
            $item->setDocComment(new Comment\Doc('// ' . $comment));
        }

        return $item;
    }

    /**
     * Returns terminal type of the given type.
     */
    protected function getDefinitionOf(TypeInterface $type): TypeInterface
    {
        if ($type instanceof GenericTypeInterface) {
            return $this->getDefinitionOf($type->getOfType());
        }

        return $type;
    }

    /**
     * Returns {@see true} in case of type is allowed to access
     * using {@see \FFI::new()}, {@see \FFI::cast()} or {@see \FFI::type()}
     * functions.
     *
     * @param list<non-empty-string> $excludes List of excluded directories.
     */
    protected function isUserAccessible(TypeDefinitionNode|FunctionNode $node, array $excludes): bool
    {
        return $node instanceof TypeDefinitionNode
            && !$node->location->matches($excludes)
        ;
    }

    /**
     * All type definitions are available for the user to use. However, if an
     * incomplete structure or union is created, the exception {@see \FFI\ParserException}
     * with message like 'Incomplete struct "<NAME>"' will be thrown.
     *
     * This method returns {@see true} if the type being created will not cause
     * such errors or {@see false} instead.
     *
     * @param list<non-empty-string> $excludes List of excluded directories.
     */
    protected function isUserCreatable(TypeDefinitionNode|FunctionNode $node, array $excludes): bool
    {
        if (!$this->isUserAccessible($node, $excludes)) {
            return false;
        }

        /** @var TypeDefinitionNode $node */
        $terminal = $this->getDefinitionOf($node);

        return !($terminal instanceof RecordTypeNode && $terminal->incomplete);
    }
}
