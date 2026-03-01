<?php

declare(strict_types=1);

namespace FFI\Generator\TypeResolver;

use FFI\CData;
use FFI\Generator\Node\Type\RecordTypeNode;
use FFI\Generator\Node\Type\TypeInterface;
use TypeLang\Parser\Node\FullQualifiedName;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final readonly class RecordTypeResolver implements TypeResolverInterface
{
    public function tryResolve(TypeInterface $type, Context $ctx): ?TypeStatement
    {
        if (!$type instanceof RecordTypeNode) {
            return null;
        }

        return new NamedTypeNode(new FullQualifiedName(CData::class));
    }
}
