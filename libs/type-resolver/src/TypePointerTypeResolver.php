<?php

declare(strict_types=1);

namespace FFI\Generator\TypeResolver;

use FFI\CData;
use FFI\Generator\Node\Type\FundamentalTypeNode;
use FFI\Generator\Node\Type\PointerTypeNode;
use FFI\Generator\Node\Type\TypeInterface;
use TypeLang\Parser\Node\FullQualifiedName;
use TypeLang\Parser\Node\Literal\NullLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

final readonly class TypePointerTypeResolver implements TypeResolverInterface
{
    public function tryResolve(TypeInterface $type, Context $ctx): ?TypeStatement
    {
        if (!$type instanceof PointerTypeNode) {
            return null;
        }

        $pointerToFundamentalType = $type->to(FundamentalTypeNode::class);

        if ($pointerToFundamentalType === null) {
            return null;
        }

        return new UnionTypeNode(
            a: new NamedTypeNode(new FullQualifiedName(CData::class)),
            b: new NullLiteralNode(),
        );
    }
}
