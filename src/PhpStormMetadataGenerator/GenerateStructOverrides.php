<?php

declare(strict_types=1);

namespace FFI\Generator\PhpStormMetadataGenerator;

use FFI\Generator\NamingStrategyInterface;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\Type\OptionalNamedTypeInterface;
use FFI\Generator\Node\Type\RecordTypeNode;
use FFI\Generator\Node\Type\StructTypeNode;
use FFI\Generator\Node\Type\TypeDefinitionNode;
use FFI\Generator\Node\Type\TypeInterface;
use PhpParser\Comment;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;

/**
 * Generates:
 *  - override(\ClassName::type(), map([ '' => '@|\PHPSTORM_META\@|\FFI\CData' ]));
 *  - override(\ClassName::new(),  map([ '' => '@|\PHPSTORM_META\@|\FFI\CData' ]));
 */
final class GenerateStructOverrides extends Visitor
{
    private const BUILTIN_TYPES = [
        'void *',
        'bool',
        'float',
        'double',
        'long double',
        'char',
        'signed char',
        'unsigned char',
        'short',
        'short int',
        'signed short',
        'signed short int',
        'unsigned short',
        'unsigned short int',
        'int',
        'signed int',
        'unsigned int',
        'long',
        'long int',
        'signed long',
        'signed long int',
        'unsigned long',
        'unsigned long int',
        'long long',
        'long long int',
        'signed long long',
        'signed long long int',
        'unsigned long long',
        'unsigned long long int',
        'intptr_t',
        'uintptr_t',
        'size_t',
        'ssize_t',
        'ptrdiff_t',
        'off_t',
        'va_list',
        '__builtin_va_list',
        '__gnuc_va_list',
        'int8_t',
        'uint8_t',
        'int16_t',
        'uint16_t',
        'int32_t',
        'uint32_t',
        'int64_t',
        'uint64_t',
    ];

    /**
     * @var list<non-empty-string>
     */
    private array $names = self::BUILTIN_TYPES;

    /**
     * @param list<non-empty-string> $excludes
     */
    public function __construct(
        private readonly NamingStrategyInterface $naming,
        private readonly string $argumentSetPrefix,
        private readonly string $globalArgumentSetSuffix = 'types_list',
        private readonly array $excludes = [],
    ) {
    }

    /**
     * @return list<non-empty-string>|null
     */
    private function getTypeAliases(TypeInterface $type): ?array
    {
        if ($type instanceof RecordTypeNode && $type->name !== null) {
            if ($type->location->matches($this->excludes)) {
                return null;
            }

            return [$type->name];
        }

        if ($type instanceof TypeDefinitionNode) {
            $parent = $this->getTypeAliases($type->type);

            if ($parent === null) {
                return null;
            }

            return [$type->name, ...$parent];
        }

        return null;
    }

    /**
     * Collect all fundamental type names
     */
    public function before(NamespaceNode $ctx, iterable $nodes): iterable
    {
        foreach ($nodes as $node) {
            if ($node instanceof TypeDefinitionNode) {
                foreach ($this->getTypeAliases($node) ?? [] as $name) {
                    if (!\in_array($name, $this->names, true)) {
                        $this->names[] = $name;
                    }
                }
            }
        }

        return [];
    }

    /**
     * Additionally, an autocomplete with a pointer will be generated if the
     * specified type is a structure-like or a typedef for a structure-like.
     *
     * @param non-empty-string $name
     *
     * @psalm-suppress RedundantConditionGivenDocblockType
     */
    private function isStructureLike(NamespaceNode $ctx, string $name): bool
    {
        if (!isset($ctx->types[$name])) {
            return false;
        }

        $type = $ctx->types[$name];

        if ($type instanceof RecordTypeNode) {
            return true;
        }

        return $type instanceof TypeDefinitionNode && $type->type instanceof RecordTypeNode;
    }

    public function after(NamespaceNode $ctx, iterable $nodes): iterable
    {
        //
        // Create types arguments list
        //

        $registerArgumentsSet = new FuncCall(
            name: new Name('registerArgumentsSet'),
            args: [new Arg(
                value: new String_(
                    value: $this->argumentSetPrefix . $this->globalArgumentSetSuffix
                ),
                attributes: ['comments' => [new Comment('// List of available FFI type names')]]
            )]
        );

        foreach ($this->names as $name) {
            $registerArgumentsSet->args[] = new Arg(new String_($name));

            if ($this->isStructureLike($ctx, $name)) {
                $registerArgumentsSet->args[] = new Arg(new String_($name . '*'));
                $registerArgumentsSet->args[] = new Arg(new String_($name . '**'));
            }
        }

        yield new Expression($registerArgumentsSet);

        //
        // Create autocomplete for "new" and "type" methods
        //

        yield new Expression(new FuncCall(
            name: new Name('expectedArguments'),
            args: [
                new Arg(new StaticCall(
                    class: new Name\FullyQualified($this->naming->getEntrypoint()),
                    name: 'new',
                )),
                new Arg(new LNumber(0)),
                new Arg(new FuncCall(
                    name: new Name('argumentsSet'),
                    args: [new Arg(new String_(
                        value: $this->argumentSetPrefix . $this->globalArgumentSetSuffix
                    ))]
                ))
            ]
        ));

        yield new Expression(new FuncCall(
            name: new Name('expectedArguments'),
            args: [
                new Arg(new StaticCall(
                    class: new Name\FullyQualified($this->naming->getEntrypoint()),
                    name: 'cast',
                )),
                new Arg(new LNumber(0)),
                new Arg(new FuncCall(
                    name: new Name('argumentsSet'),
                    args: [new Arg(new String_(
                        value: $this->argumentSetPrefix . $this->globalArgumentSetSuffix
                    ))]
                ))
            ]
        ));

        yield new Expression(new FuncCall(
            name: new Name('expectedArguments'),
            args: [
                new Arg(new StaticCall(
                    class: new Name\FullyQualified($this->naming->getEntrypoint()),
                    name: 'type',
                )),
                new Arg(new LNumber(0)),
                new Arg(new FuncCall(
                    name: new Name('argumentsSet'),
                    args: [new Arg(new String_(
                        value: $this->argumentSetPrefix . $this->globalArgumentSetSuffix
                    ))]
                ))
            ]
        ));

        //
        // Create override for "new" and "type" methods
        //

        $internalName = \rtrim($this->naming->getName('@', new StructTypeNode('@')), '@') . '@';

        $map = [new ArrayItem(
            value: new String_('\\' . $internalName),
            key: new String_(''),
            attributes: ['comments' => [new Comment('// structures autocompletion')]]
        )];

        foreach ($nodes as $node) {
            if ($node instanceof TypeDefinitionNode && $node->type instanceof RecordTypeNode) {
                if ($node->location->matches($this->excludes)) {
                    continue;
                }

                $mappedType = $this->naming->getName(
                    name: $node->name,
                    type: $node->type,
                );

                $map[] = new ArrayItem(
                    value: new String_("\\$mappedType"),
                    key: new String_($node->name),
                );

                if ($this->isStructureLike($ctx, $node->name)) {
                    $map[] = new ArrayItem(
                        value: new String_("\\{$mappedType}[]"),
                        key: new String_( $node->name . '*'),
                    );
                    $map[] = new ArrayItem(
                        value: new String_("\\{$mappedType}"),
                        key: new String_( $node->name . '*'),
                    );
                    $map[] = new ArrayItem(
                        value: new String_("\\{$mappedType}[][]"),
                        key: new String_( $node->name . '**'),
                    );
                    $map[] = new ArrayItem(
                        value: new String_("\\{$mappedType}[]"),
                        key: new String_( $node->name . '**'),
                    );
                    $map[] = new ArrayItem(
                        value: new String_("\\{$mappedType}"),
                        key: new String_( $node->name . '**'),
                    );
                }
            }
        }

        yield new Expression(new FuncCall(
            name: new Name('override'),
            args: [
                new Arg(new StaticCall(
                    class: new Name\FullyQualified($this->naming->getEntrypoint()),
                    name: 'new',
                )),
                new Arg(new FuncCall(
                    name: new Name('map'),
                    args: [new Arg(new Array_(
                        items: $map,
                        attributes: ['kind' => Array_::KIND_SHORT],
                    ))]
                ))
            ]
        ));

        $this->names = [];
    }
}
