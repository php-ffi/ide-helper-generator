<?php

declare(strict_types=1);

namespace FFI\Generator\TypeResolver;

use FFI\Generator\Node\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

interface TypeResolverInterface
{
    public function tryResolve(TypeInterface $type, Context $ctx): ?TypeStatement;
}
