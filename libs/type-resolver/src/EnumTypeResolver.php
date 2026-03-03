<?php

declare(strict_types=1);

namespace FFI\Generator\TypeResolver;

use FFI\Generator\Node\Type\EnumerationTypeNode;
use FFI\Generator\Node\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final readonly class EnumTypeResolver implements TypeResolverInterface
{
    public function tryResolve(TypeInterface $type, Context $ctx): ?TypeStatement
    {
        if (!$type instanceof EnumerationTypeNode) {
            return null;
        }

        return new NamedTypeNode('int');
    }
}
