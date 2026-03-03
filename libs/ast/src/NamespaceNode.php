<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

use FFI\Generator\Node\Type\StructTypeNode;
use FFI\Generator\Node\Type\TypeDefinitionTypeNode;
use FFI\Generator\Node\Type\TypeInterface;

final class NamespaceNode extends Node
{
    /**
     * @var list<TypeInterface>
     */
    public array $types = [];

    /**
     * @var list<FunctionNode>
     */
    public array $functions = [];

    /**
     * @var list<VariableNode>
     */
    public array $variables = [];

    /**
     * @var iterable<array-key, TypeDefinitionTypeNode<TypeInterface>>
     */
    public iterable $typedefs {
        get {
            foreach ($this->types as $typedef) {
                if (!$typedef instanceof TypeDefinitionTypeNode) {
                    continue;
                }

                yield $typedef;
            }
        }
    }

    /**
     * @var iterable<array-key, TypeDefinitionTypeNode<StructTypeNode>>
     */
    public iterable $structures {
        get {
            foreach ($this->typedefs as $typedef) {
                if (!$typedef->type instanceof StructTypeNode) {
                    continue;
                }

                yield $typedef;
            }
        }
    }

    public function __construct(
        /**
         * @var non-empty-string|null
         */
        public readonly ?string $name = null,
    ) {}
}
