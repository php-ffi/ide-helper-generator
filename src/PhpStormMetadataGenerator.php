<?php

declare(strict_types=1);

namespace FFI\Generator;

use FFI\Generator\PhpStormMetadataGenerator\GenerateExportFunctions;
use FFI\Generator\PhpStormMetadataGenerator\GenerateOverrides;
use FFI\Generator\PhpStormMetadataGenerator\GenerateStructures;
use FFI\Generator\PhpStormMetadataGenerator\GenerateTypesInstantiation;
use FFI\Generator\PhpStormMetadataGenerator\Visitor;
use FFI\Generator\PhpStormMetadataGenerator\GenerateEnumArgumentsSet;
use FFI\Generator\PhpStormMetadataGenerator\GenerateEnumExpectedArguments;
use FFI\Generator\PhpStormMetadataGenerator\GenerateEnumExpectedReturnValues;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;

final class PhpStormMetadataGenerator implements GeneratorInterface
{
    /**
     * @var list<Visitor>
     */
    private array $phpstormMetadataVisitors = [];

    /**
     * @var list<Visitor>
     */
    private array $entrypointMetadataVisitors = [];

    /**
     * @param non-empty-string $argumentSetPrefix
     * @param non-empty-string $globalTypesArgumentSetSuffix
     * @param int<0, max> $pointersInheritance
     * @param list<non-empty-string> $ignoreDirectories
     */
    public function __construct(
        string $argumentSetPrefix = 'ffi_',
        string $globalTypesArgumentSetSuffix = 'types_list',
        int $pointersInheritance = 2,
        bool $allowScalarOverrides = true,
        array $ignoreDirectories = [ '/usr' ],
        private readonly NamingStrategyInterface $naming = new SimpleNamingStrategy(),
    ) {
        $this->phpstormMetadataVisitors[] = new GenerateTypesInstantiation(
            naming: $this->naming,
            argumentSetPrefix: $argumentSetPrefix,
            globalArgumentSetSuffix: $globalTypesArgumentSetSuffix,
            excludes: $ignoreDirectories,
            pointersInheritance: $pointersInheritance
        );
        $this->phpstormMetadataVisitors[] = new GenerateOverrides(
            naming: $this->naming,
            excludes: $ignoreDirectories,
            pointersInheritance: $pointersInheritance,
            allowScalarOverrides: $allowScalarOverrides,
        );
        $this->phpstormMetadataVisitors[] = new GenerateStructures(
            naming: $this->naming,
            excludes: $ignoreDirectories,
        );

        //
        // Enum autocomplete
        //

        $this->phpstormMetadataVisitors[] = new GenerateEnumArgumentsSet(
            naming: $this->naming,
            argumentSetPrefix: $argumentSetPrefix,
            excludes: $ignoreDirectories,
        );
        $this->phpstormMetadataVisitors[] = new GenerateEnumExpectedArguments(
            naming: $this->naming,
            argumentSetPrefix: $argumentSetPrefix,
            excludes: $ignoreDirectories,
        );
        $this->phpstormMetadataVisitors[] = new GenerateEnumExpectedReturnValues(
            naming: $this->naming,
            argumentSetPrefix: $argumentSetPrefix,
            excludes: $ignoreDirectories,
        );

        //
        // Library main API
        //

        $this->entrypointMetadataVisitors[] = new GenerateExportFunctions(
            naming: $this->naming,
            excludes: $ignoreDirectories,
        );
    }

    public function generate(iterable $namespaces): ResultInterface
    {
        $phpstormMetadata = new Namespace_(
            name: new Name('PHPSTORM_META'),
            attributes: ['kind' => Namespace_::KIND_BRACED],
        );

        $externalMetadata = new Namespace_(
            name: new Name($this->naming->getEntrypointNamespace()),
            attributes: ['kind' => Namespace_::KIND_BRACED],
        );

        foreach ($namespaces as $namespace) {
            // Skip non-global namespaces
            if ($namespace->name !== null) {
                continue;
            }

            $members = \iterator_to_array($namespace->getMembers(), false);

            foreach ($this->phpstormMetadataVisitors as $extension) {
                foreach ($extension->before($namespace, $members) as $stmt) {
                    $phpstormMetadata->stmts[] = $stmt;
                }

                foreach ($members as $member) {
                    foreach ($extension->enter($namespace, $member) as $stmt) {
                        $phpstormMetadata->stmts[] = $stmt;
                    }
                }

                foreach ($extension->after($namespace, $members) as $stmt) {
                    $phpstormMetadata->stmts[] = $stmt;
                }
            }

            foreach ($this->entrypointMetadataVisitors as $extension) {
                foreach ($extension->before($namespace, $members) as $stmt) {
                    $externalMetadata->stmts[] = $stmt;
                }

                foreach ($members as $member) {
                    foreach ($extension->enter($namespace, $member) as $stmt) {
                        $externalMetadata->stmts[] = $stmt;
                    }
                }

                foreach ($extension->after($namespace, $members) as $stmt) {
                    $externalMetadata->stmts[] = $stmt;
                }
            }
        }

        return new NikicPrinterResult($this->naming, [
            $phpstormMetadata,
            $externalMetadata,
        ]);
    }
}
