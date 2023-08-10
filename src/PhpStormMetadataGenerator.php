<?php

declare(strict_types=1);

namespace FFI\Generator;

use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\PhpStormMetadataGenerator\GenerateExportFunctions;
use FFI\Generator\PhpStormMetadataGenerator\GenerateStructOverrides;
use FFI\Generator\PhpStormMetadataGenerator\GenerateStructures;
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
     * @param list<non-empty-string> $ignoreDirectories
     * @param NamingStrategyInterface $naming
     */
    public function __construct(
        string $argumentSetPrefix = 'ffi_',
        array $ignoreDirectories = [ '/usr' ],
        private readonly NamingStrategyInterface $naming = new SimpleNamingStrategy(),
    ) {
        $this->phpstormMetadataVisitors[] = new GenerateEnumArgumentsSet($this->naming, $argumentSetPrefix);
        $this->phpstormMetadataVisitors[] = new GenerateEnumExpectedArguments($this->naming, $argumentSetPrefix);
        $this->phpstormMetadataVisitors[] = new GenerateEnumExpectedReturnValues($this->naming, $argumentSetPrefix);
        $this->phpstormMetadataVisitors[] = new GenerateStructOverrides(
            naming: $this->naming,
            argumentSetPrefix: $argumentSetPrefix,
            ignoreDirectories: $ignoreDirectories,
        );
        $this->phpstormMetadataVisitors[] = new GenerateStructures($this->naming, $ignoreDirectories);

        $this->entrypointMetadataVisitors[] = new GenerateExportFunctions($this->naming, $ignoreDirectories);
    }

    public function generate(iterable $namespaces): ResultInterface
    {
        $phpstormMetadata = new Namespace_(
            name: new Name('PHPSTORM_META'), attributes: ['kind' => Namespace_::KIND_BRACED],
        );

        $externalMetadata = new Namespace_(
            name: new Name($this->naming->getEntrypointNamespace()), attributes: ['kind' => Namespace_::KIND_BRACED],
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
