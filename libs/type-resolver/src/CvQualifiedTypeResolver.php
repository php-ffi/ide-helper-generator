<?php

declare(strict_types=1);

namespace FFI\Generator\TypeResolver;

use FFI\Generator\Node\Type\TypeInterface;
use FFI\Generator\Node\Type\TypeModifierInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final readonly class CvQualifiedTypeResolver implements TypeResolverInterface
{
    public function tryResolve(TypeInterface $type, Context $ctx): ?TypeStatement
    {
        if ($type instanceof TypeModifierInterface) {
            return $ctx->resolver->tryResolve($type->type, $ctx);
        }

        return null;
    }
}
