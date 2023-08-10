<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

use FFI\Generator\Node\Type\TypeDefinitionNode;

final class NamespaceNode extends OptionalNamedNode
{
    public const DEFAULT_GLOBAL_NAMESPACE = '::';

    /**
     * @var array<non-empty-string, TypeDefinitionNode>
     */
    #[Visitable]
    public array $types = [];

    /**
     * @var array<non-empty-string, FunctionNode>
     */
    #[Visitable]
    public array $functions = [];

    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name = self::DEFAULT_GLOBAL_NAMESPACE)
    {
        if ($name === self::DEFAULT_GLOBAL_NAMESPACE) {
            $name = null;
        }

        parent::__construct($name);
    }

    /**
     * @return \Iterator<array-key, TypeDefinitionNode|FunctionNode>
     */
    public function getMembers(): \Iterator
    {
        yield from $this->types;
        yield from $this->functions;
    }
}
