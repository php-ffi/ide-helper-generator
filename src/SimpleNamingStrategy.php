<?php

declare(strict_types=1);

namespace FFI\Generator;

use FFI\Generator\Node\Type\ClassTypeNode;
use FFI\Generator\Node\Type\EnumTypeNode;
use FFI\Generator\Node\EnumValueNode;
use FFI\Generator\Node\OptionalNamedNodeInterface;
use FFI\Generator\Node\Type\StructTypeNode;
use FFI\Generator\Node\Type\UnionTypeNode;

class SimpleNamingStrategy implements NamingStrategyInterface
{
    /**
     * @param non-empty-string $entrypoint
     */
    public function __construct(
        public readonly string $entrypoint = 'FFI\\Generated\\EntrypointInterface',
        public readonly string $externalNamespace = 'FFI\\Generated',
        public readonly string $internalNamespace = 'PHPSTORM_META',
    ) {}

    public function getEntrypoint(): string
    {
        return $this->entrypoint;
    }

    public function getEntrypointNamespace(): string
    {
        $parts = \explode('\\', $this->getEntrypoint());

        return \implode('\\', \array_slice($parts, 0, -1));
    }

    public function getEntrypointClassName(): string
    {
        $parts = \explode('\\', $this->getEntrypoint());

        /** @var non-empty-string */
        return \end($parts);
    }

    protected function getExternalNamespacePrefix(): string
    {
        return \trim($this->externalNamespace, '\\') . '\\';
    }

    protected function getInternalNamespacePrefix(): string
    {
        return \trim($this->internalNamespace, '\\') . '\\';
    }

    protected function toSnakeCase(string $name): string
    {
        return \preg_replace('/([a-z])([A-Z])/u', '$1_$2', $name);
    }

    /**
     * @param string $name
     * @return ($name is non-empty-string ? non-empty-lowercase-string : lowercase-string)
     */
    protected function toLowerSnakeCase(string $name): string
    {
        return \strtolower($this->toSnakeCase($name));
    }

    protected function toUpperSnakeCase(string $name): string
    {
        return \strtoupper($this->toSnakeCase($name));
    }

    protected function toCamelCase(string $name): string
    {
        $lower = $this->toSnakeCase($name);

        return \ucfirst(\str_replace('_', '', \ucwords($lower, '_')));
    }

    /**
     * @param non-empty-string $name
     * @return non-empty-string
     */
    protected function getEnumTypeName(string $name): string
    {
        /** @var non-empty-string */
        return $this->getExternalNamespacePrefix()
            . $this->toCamelCase($name)
        ;
    }

    /**
     * @param non-empty-string $name
     * @return non-empty-string
     */
    protected function getStructTypeName(string $name): string
    {
        /** @var non-empty-string */
        return $this->getInternalNamespacePrefix()
            . $this->toCamelCase($name)
        ;
    }

    /**
     * @param non-empty-string $name
     * @return non-empty-string
     */
    protected function getUnionTypeName(string $name): string
    {
        /** @var non-empty-string */
        return $this->getInternalNamespacePrefix()
            . $this->toCamelCase($name)
        ;
    }

    /**
     * @param non-empty-string $name
     * @return non-empty-string
     */
    protected function getClassTypeName(string $name): string
    {
        /** @var non-empty-string */
        return $this->getInternalNamespacePrefix()
            . $this->toCamelCase($name)
        ;
    }

    /**
     * @param non-empty-string $name
     * @return non-empty-string
     */
    protected function getEnumValueName(string $name): string
    {
        /** @var non-empty-string */
        return $this->toUpperSnakeCase($name);
    }

    public function getName(string $name, OptionalNamedNodeInterface $type): string
    {
        return match (true) {
            $type instanceof EnumTypeNode => $this->getEnumTypeName($name),
            $type instanceof StructTypeNode => $this->getStructTypeName($name),
            $type instanceof UnionTypeNode => $this->getUnionTypeName($name),
            $type instanceof ClassTypeNode => $this->getClassTypeName($name),
            $type instanceof EnumValueNode => $this->getEnumValueName($name),
            default => $name,
        };
    }
}
