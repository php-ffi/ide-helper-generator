<?php

declare(strict_types=1);

namespace FFI\Generator\TypeResolver;

use FFI\Generator\Node\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final readonly class TypeResolver implements TypeResolverInterface
{
    /**
     * @var list<TypeResolverInterface>
     */
    private array $resolvers;

    /**
     * @param iterable<mixed, TypeResolverInterface> $resolvers
     */
    public function __construct(iterable $resolvers)
    {
        $this->resolvers = \iterator_to_array($resolvers, false);
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

    public function resolve(TypeInterface $type, Direction $direction): TypeStatement
    {
        $result = $this->tryResolve($type, new Context(
            direction: $direction,
            types: $this,
        ));

        if ($result === null) {
            return new NamedTypeNode('mixed');
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
