<?php

declare(strict_types=1);

namespace FFI\Generator\TypeResolver;

use FFI\Generator\Node\Type\TypeDefinitionTypeNode;
use FFI\Generator\Node\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final readonly class TypeDefinitionTypeResolver implements TypeResolverInterface
{
    public function tryResolve(TypeInterface $type, Context $ctx): ?TypeStatement
    {
        $isTypeDef = $type instanceof TypeDefinitionTypeNode;

        if ($isTypeDef) {
            return $ctx->types->tryResolve($type->type, $ctx);
        }

        return null;
    }
}
