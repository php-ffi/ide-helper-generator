<?php

declare(strict_types=1);

namespace FFI\Generator\PhpStormMetadataGenerator;

use FFI\Generator\Generator\TypeInfoGenerator;
use FFI\Generator\NamingStrategyInterface;
use FFI\Generator\Node\FunctionNode;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\Type\ClassTypeNode;
use FFI\Generator\Node\Type\RecordTypeNode;
use FFI\Generator\Node\Type\StructTypeNode;
use FFI\Generator\Node\Type\TypeDefinitionNode;
use FFI\Generator\Node\Type\UnionTypeNode;
use FFI\CData;
use PhpParser\Builder\Method;
use PhpParser\Builder\Property;
use PhpParser\Comment;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;

/**
 * Generates structure layouts.
 */
final class GenerateStructures extends Visitor
{
    private readonly TypeInfoGenerator $info;

    /**
     * @param list<non-empty-string> $excludes
     */
    public function __construct(
        private readonly NamingStrategyInterface $naming,
        private readonly array $excludes = [],
    ) {
        $this->info = new TypeInfoGenerator($naming);
    }

    public function enter(NamespaceNode $ctx, FunctionNode|TypeDefinitionNode $node): iterable
    {
        if (!$node instanceof TypeDefinitionNode
            || !$node->type instanceof RecordTypeNode
            || $node->location->matches($this->excludes)) {
            return;
        }

        $phpStructFullQualifiedName = new Name\FullyQualified(
            name: $this->naming->getName($node->name, $node->type),
        );

        $entry = $this->naming->getEntrypoint();

        $type = match (true) {
            $node->type instanceof StructTypeNode => 'structure',
            $node->type instanceof UnionTypeNode => 'union',
            $node->type instanceof ClassTypeNode => 'class',
            default => 'record'
        };

        $structLayout = new Class_(
            name: new Identifier($phpStructFullQualifiedName->getLast()),
            attributes: [
                'comments' => [new Comment\Doc(<<<PHPDOC
                    /**
                     * Generated "$node->name" $type layout.
                     *
                     * @ignore
                     * @internal Internal interface to ensure precise type inference.
                     */
                    PHPDOC)]
            ]
        );

        $structLayout->flags = Class_::MODIFIER_FINAL;
        $structLayout->extends = new Name\FullyQualified(CData::class);

        foreach ($node->type->fields as $i => $field) {
            $typeInfo = $this->info->get($ctx, $field->type);
            $phpDocTypeString = $typeInfo->getDocTypeAsString();

            $property = (new Property($field->name ?? '_' . $i))
                ->setType($typeInfo->getPhpTypeAsString())
                ->makePublic();

            if ($phpDocTypeString && $phpDocTypeString !== 'mixed') {
                $property->setDocComment(<<<PHPDOC
                    /**
                     * @var $phpDocTypeString
                     */
                    PHPDOC);
            }

            if ($typeInfo->const) {
                $property->makeReadonly();
            }

            $structLayout->stmts[] = $property->getNode();
        }

        $structLayout->stmts[] = (new Method('__construct'))
            ->setDocComment(<<<PHPDOC
                /**
                 * @internal Please use {@see \\$entry::new()} with '$node->name' argument instead.
                 */
                PHPDOC)
            ->makePrivate()
            ->getNode();

        yield $structLayout;
    }
}
