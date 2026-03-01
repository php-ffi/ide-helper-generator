<?php

declare(strict_types=1);

namespace FFI\Generator\TypeResolver;

use FFI\Generator\Node\Type\ConstTypeNode;
use FFI\Generator\Node\Type\RestrictTypeNode;
use FFI\Generator\Node\Type\TypeInterface;
use FFI\Generator\Node\Type\VolatileTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final readonly class CvQualifiedTypeResolver implements TypeResolverInterface
{
    public function tryResolve(TypeInterface $type, Context $ctx): ?TypeStatement
    {
        $isCvQualified = $type instanceof ConstTypeNode
            || $type instanceof VolatileTypeNode
            || $type instanceof RestrictTypeNode;

        if ($isCvQualified) {
            return $ctx->types->tryResolve($type->type, $ctx);
        }

        return null;
    }
}
