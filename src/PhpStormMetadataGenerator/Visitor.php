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
     * List of builtin types and its sizes.
     *
     * @var array<non-empty-string, int<1, max>>
     */
    public const BUILTIN_TYPES = [
        'void *' => 8,
        'bool' => 1,
        'float' => 4,
        'double' => 8,
        'long double' => 8,
        'char' => 1,
        'signed char' => 1,
        'unsigned char' => 1,
        'short' => 2,
        'short int' => 2,
        'signed short' => 2,
        'signed short int' => 2,
        'unsigned short' => 2,
        'unsigned short int' => 2,
        'int' => 4,
        'signed int' => 4,
        'unsigned int' => 4,
        'long' => 4,
        'long int' => 4,
        'signed long' => 4,
        'signed long int' => 4,
        'unsigned long' => 4,
        'unsigned long int' => 4,
        'long long' => 8,
        'long long int' => 8,
        'signed long long' => 8,
        'signed long long int' => 8,
        'unsigned long long' => 8,
        'unsigned long long int' => 8,
        'intptr_t' => 8,
        'uintptr_t' => 8,
        'size_t' => 8,
        'ssize_t' => 8,
        'ptrdiff_t' => 8,
        'off_t' => 4,
        'va_list' => 8,
        '__builtin_va_list' => 8,
        '__gnuc_va_list' => 8,
        'int8_t' => 1,
        'uint8_t' => 1,
        'int16_t' => 2,
        'uint16_t' => 2,
        'int32_t' => 4,
        'uint32_t' => 4,
        'int64_t' => 8,
        'uint64_t' => 8,
    ];

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
