<?php

declare(strict_types=1);

namespace FFI\Generator\TypeResolver\Factory;

use FFI\Generator\Node\Type\TypeInterface;
use FFI\Generator\TypeResolver\Direction;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TCType of TypeInterface = TypeInterface
 * @template-covariant TPhpType of TypeStatement = TypeStatement
 */
interface TypeFactoryInterface
{
    /**
     * @param TCType $type
     * @return TPhpType
     */
    public function create(TypeInterface $type, Direction $direction): TypeStatement;
}
