<?php

declare(strict_types=1);

namespace FFI\Generator\TypeResolver;

use FFI\Generator\Node\Type\FundamentalTypeNode;
use FFI\Generator\Node\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final readonly class FundamentalTypeResolver implements TypeResolverInterface
{
    private const int PHP_MAX_SUPPORTED_BITS = \PHP_INT_SIZE * 8;

    public function tryResolve(TypeInterface $type, Context $ctx): ?TypeStatement
    {
        if (!$type instanceof FundamentalTypeNode) {
            return null;
        }

        $isUnsigned = \str_contains($type->name, 'unsigned');

        return match ($type->name) {
            'void' => new NamedTypeNode('void'),
            'bool' => new NamedTypeNode('bool'),
            'float', 'double', 'long double' => new NamedTypeNode('float'),
            'char' => new NamedTypeNode('non-empty-string'),
            default => new NamedTypeNode('int', new TemplateArgumentsListNode([
                new TemplateArgumentNode(new NamedTypeNode(
                    name: $this->getMinBound($type->size, $isUnsigned),
                )),
                new TemplateArgumentNode(new NamedTypeNode(
                    name: $this->getMaxBound($type->size, $isUnsigned),
                )),
            ])),
        };
    }

    /**
     * @param int<0, max> $size
     * @return numeric-string
     */
    private function getMaxBound(int $size, bool $unsigned): string
    {
        if ($size >= self::PHP_MAX_SUPPORTED_BITS) {
            return 'max';
        }

        if ($unsigned === false) {
            --$size;
        }

        return (string) ((2 ** $size) - 1);
    }

    /**
     * @param int<0, max> $size
     * @return numeric-string
     */
    private function getMinBound(int $size, bool $unsigned): string
    {
        if ($unsigned) {
            return '0';
        }

        if ($size >= self::PHP_MAX_SUPPORTED_BITS) {
            return 'min';
        }

        return (string) (2 ** ($size - 1) * -1);
    }
}
