<?php

declare(strict_types=1);

namespace FFI\Generator\TypeResolver;

use FFI\Generator\Node\Type\FundamentalTypeNode;
use FFI\Generator\Node\Type\PointerTypeNode;
use FFI\Generator\Node\Type\TypeInterface;
use TypeLang\Parser\Node\Literal\NullLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

final readonly class StringTypeResolver implements TypeResolverInterface
{
    public function tryResolve(TypeInterface $type, Context $ctx): ?TypeStatement
    {
        if (!$type instanceof PointerTypeNode) {
            return null;
        }

        $pointerTarget = $type->to(FundamentalTypeNode::class);

        if ($pointerTarget?->name === 'char') {
            return new UnionTypeNode(
                a: new NamedTypeNode('string'),
                b: new NullLiteralNode(),
            );
        }

        return null;
    }
}
