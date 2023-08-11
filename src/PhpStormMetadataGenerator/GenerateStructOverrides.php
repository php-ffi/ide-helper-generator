<?php

declare(strict_types=1);

namespace FFI\Generator\PhpStormMetadataGenerator;

use FFI\Generator\Generator\TypeInfoGenerator;
use FFI\Generator\NamingStrategyInterface;
use FFI\Generator\Node\FunctionNode;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\Type\EnumTypeNode;
use FFI\Generator\Node\Type\FundamentalTypeNode;
use FFI\Generator\Node\Type\RecordTypeNode;
use FFI\Generator\Node\Type\StructTypeNode;
use FFI\Generator\Node\Type\TypeDefinitionNode;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\Expression;

/**
 * Generates:
 *  - override(\FFI::new(), map(['' => '\PHPSTORM_META\@', ...]));
 */
final class GenerateStructOverrides extends Visitor
{
    /**
     * @var non-empty-string
     */
    private const COMMENT = 'List of return type coercions';

    private readonly TypeInfoGenerator $info;

    /**
     * @param list<non-empty-string> $excludes
     */
    public function __construct(
        private readonly NamingStrategyInterface $naming,
        private readonly array $excludes = [],
        private readonly int $pointersInheritance = 2,
        private readonly bool $allowScalarOverrides = true,
    ) {
        $this->info = new TypeInfoGenerator($this->naming);
    }

    /**
     * @return iterable<array-key, ArrayItem>
     */
    private function getRecordMappings(TypeDefinitionNode $node, RecordTypeNode $type): iterable
    {
        $mappedIntoName = '\\' . $this->naming->getName($node->name, $type);

        yield $this->arrayItemNode($node->name, $mappedIntoName);

        for ($pointersCount = 1; $pointersCount <= $this->pointersInheritance; ++$pointersCount) {
            for ($arrayDepthCount = 1; $arrayDepthCount <= $pointersCount; ++$arrayDepthCount) {
                yield $this->arrayItemNode(
                    key: $node->name . \str_repeat('*', $pointersCount),
                    value: $mappedIntoName . \str_repeat('[]', $arrayDepthCount)
                );
            }
        }
    }

    /**
     * @return iterable<array-key, ArrayItem>
     */
    private function getEnumTypeMapping(TypeDefinitionNode $node, EnumTypeNode $type): iterable
    {
        // TODO
        return [];
    }

    /**
     * @return iterable<array-key, ArrayItem>
     */
    private function getScalarTypeMapping(TypeDefinitionNode $node, FundamentalTypeNode $type): iterable
    {
        // TODO
        return [];
    }

    /**
     * @return iterable<array-key, ArrayItem>
     */
    private function getBuiltinTypesMapping(): iterable
    {
        // TODO
        return [];
    }

    /**
     * @param iterable<FunctionNode|TypeDefinitionNode> $nodes
     * @return iterable<ArrayItem>
     */
    private function getMappings(iterable $nodes): iterable
    {
        foreach ($nodes as $node) {
            // Skip non-accessible types
            if (!$this->isUserAccessible($node, $this->excludes)) {
                continue;
            }

            /** @var TypeDefinitionNode $node */
            $reference = $this->getDefinitionOf($node);

            if ($reference instanceof RecordTypeNode) {
                yield from $this->getRecordMappings($node, $reference);
            }

            if ($this->allowScalarOverrides) {
                yield from match (true) {
                    $reference instanceof EnumTypeNode => $this->getEnumTypeMapping($node, $reference),
                    $reference instanceof FundamentalTypeNode => $this->getScalarTypeMapping($node, $reference),
                    default => [],
                };
            }
        }

        if ($this->allowScalarOverrides) {
            yield from $this->getBuiltinTypesMapping();
        }
    }

    public function after(NamespaceNode $ctx, iterable $nodes): iterable
    {
        // Add "map(['' => '\InternalNamespace\@'])" mappings.
        $mappings = [
            $this->arrayItemNode('', $this->internalNameOf('@'), self::COMMENT),
        ];

        // Add other "map(['StructName' => '\Exte'])" mappings
        foreach ($this->getMappings($nodes) as $item) {
            $mappings[] = $item;
        }

        yield new Expression($this->override(
            class: $this->naming->getEntrypoint(),
            method: 'new',
            into: $this->map($mappings),
        ));
    }

    /**
     * @param non-empty-string $name
     * @return non-empty-string
     */
    private function internalNameOf(string $name): string
    {
        return '\\' . $this->naming->getName($name, new StructTypeNode($name));
    }
}
