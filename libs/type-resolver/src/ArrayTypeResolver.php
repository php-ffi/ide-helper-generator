<?php

declare(strict_types=1);

namespace FFI\Generator\TypeResolver;

use FFI\Generator\Node\Type\ArrayTypeNode;
use FFI\Generator\Node\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Shape\FieldsListNode;
use TypeLang\Parser\Node\Stmt\Shape\ImplicitFieldNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentsListNode;

final readonly class ArrayTypeResolver implements TypeResolverInterface
{
    public const int MAX_ARRAY_SHAPE_ITEMS = 8;

    public function __construct(
        /**
         * @var int<0, max>
         */
        private int $maxArrayShapeItems = self::MAX_ARRAY_SHAPE_ITEMS,
    ) {}

    public function tryResolve(TypeInterface $type, Context $ctx): ?NamedTypeNode
    {
        if (!$type instanceof ArrayTypeNode) {
            return null;
        }

        if ($this->isDescribedByArrayShape($type)) {
            return $this->createArrayShape($type, $ctx);
        }

        return $this->createList($type, $ctx);
    }

    private function isDescribedByArrayShape(ArrayTypeNode $type): bool
    {
        return \is_int($type->size)
            && $type->size > 0
            && $type->size <= $this->maxArrayShapeItems;
    }

    private function createTypeName(ArrayTypeNode $type): string
    {
        if ($type->size > 0) {
            return 'non-empty-list';
        }

        return 'list';
    }

    private function createArrayShape(ArrayTypeNode $type, Context $ctx): NamedTypeNode
    {
        $fields = [];

        for ($i = 0; $i < $type->size; ++$i) {
            $fields[] = new ImplicitFieldNode(
                type: $ctx->factory->create(
                    type: $type->type,
                    direction: $ctx->direction,
                ),
            );
        }

        return new NamedTypeNode(
            name: $this->createTypeName($type),
            fields: new FieldsListNode($fields),
        );
    }

    private function createList(ArrayTypeNode $type, Context $ctx): NamedTypeNode
    {
        return new NamedTypeNode(
            name: $this->createTypeName($type),
            arguments: new TemplateArgumentsListNode([
                new TemplateArgumentNode(
                    value: $ctx->factory->create(
                        type: $type->type,
                        direction: $ctx->direction,
                    ),
                ),
            ]),
        );
    }
}
