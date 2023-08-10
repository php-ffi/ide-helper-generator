<?php

declare(strict_types=1);

namespace FFI\Generator\PhpStormMetadataGenerator;

use FFI\Generator\Node\FunctionNode;
use FFI\Generator\Node\LocationNode;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\Type\TypeDefinitionNode;
use PhpParser\Node\Stmt;

abstract class Visitor
{
    /**
     * @param iterable<TypeDefinitionNode|FunctionNode> $nodes
     * @return iterable<Stmt>
     */
    public function before(NamespaceNode $ctx, iterable $nodes): iterable
    {
        return [];
    }

    /**
     * @param iterable<TypeDefinitionNode|FunctionNode> $nodes
     * @return iterable<Stmt>
     */
    public function after(NamespaceNode $ctx, iterable $nodes): iterable
    {
        return [];
    }

    /**
     * @return iterable<Stmt>
     */
    public function enter(NamespaceNode $ctx, TypeDefinitionNode|FunctionNode $node): iterable
    {
        return [];
    }

    /**
     * @param list<non-empty-string> $ignoreDirectories
     */
    protected function locationMatches(?LocationNode $location, array $ignoreDirectories): bool
    {
        return $location === null || $location->matches($ignoreDirectories);
    }
}
