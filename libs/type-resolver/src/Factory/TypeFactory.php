<?php

declare(strict_types=1);

namespace FFI\Generator\TypeResolver\Factory;

use FFI\Generator\Node\Type\TypeInterface;
use FFI\Generator\TypeResolver\Context;
use FFI\Generator\TypeResolver\CvQualifiedTypeResolver;
use FFI\Generator\TypeResolver\Direction;
use FFI\Generator\TypeResolver\EnumTypeResolver;
use FFI\Generator\TypeResolver\FundamentalTypeResolver;
use FFI\Generator\TypeResolver\RecordPointerTypeResolver;
use FFI\Generator\TypeResolver\RecordTypeResolver;
use FFI\Generator\TypeResolver\StringTypeResolver;
use FFI\Generator\TypeResolver\TypeDefinitionTypeResolver;
use FFI\Generator\TypeResolver\TypeResolverInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final readonly class TypeFactory implements
    TypeFactoryInterface,
    TypeResolverInterface
{
    /**
     * @var list<TypeResolverInterface>
     */
    private array $resolvers;

    /**
     * @var \WeakMap<Direction, Context>
     */
    private \WeakMap $contexts;

    /**
     * @param iterable<mixed, TypeResolverInterface> $resolvers
     */
    public function __construct(
        iterable $resolvers = [],
        private TypeStatement $default = new NamedTypeNode('mixed'),
    ) {
        $this->resolvers = \iterator_to_array($resolvers, false);
        $this->contexts = new \WeakMap();
    }

    public static function default(): self
    {
        return new self([
            new FundamentalTypeResolver(),
            new StringTypeResolver(),
            new CvQualifiedTypeResolver(),
            new RecordPointerTypeResolver(),
            new RecordTypeResolver(),
            new EnumTypeResolver(),
            new TypeDefinitionTypeResolver(),
        ]);
    }

    private function getContext(Direction $direction): Context
    {
        return $this->contexts[$direction] ??= new Context(
            direction: $direction,
            resolver: $this,
            factory: $this,
        );
    }

    public function create(TypeInterface $type, Direction $direction): TypeStatement
    {
        $result = $this->tryResolve($type, $this->getContext($direction));

        if ($result === null) {
            return clone $this->default;
        }

        return $result;
    }

    public function tryResolve(TypeInterface $type, Context $ctx): ?TypeStatement
    {
        foreach ($this->resolvers as $resolver) {
            $result = $resolver->tryResolve($type, $ctx);

            if ($result === null) {
                continue;
            }

            return $result;
        }

        return null;
    }
}
