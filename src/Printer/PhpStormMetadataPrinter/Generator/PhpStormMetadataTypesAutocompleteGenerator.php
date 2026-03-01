<?php

declare(strict_types=1);

namespace FFI\Generator\Printer\PhpStormMetadataPrinter\Generator;

use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\Type\OptionalNamedTypeInterface;
use FFI\Generator\Printer\PhpStormMetadataPrinter\PhpStormMetadataBuilder;
use FFI\Generator\Support\BuiltinTypes;
use PhpParser\Node\Arg;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt as PhpStatement;
use PhpParser\Node\Stmt\Expression;
use Random\Randomizer;

/**
 * Generates string autocompletion for {@see \FFI::new()}, {@see \FFI::cast()}
 * and {@see \FFI::type()} methods.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Generator\Printer
 */
final readonly class PhpStormMetadataTypesAutocompleteGenerator
{
    /**
     * @var non-empty-string
     */
    private string $argumentSetName;

    public function __construct(
        private PhpStormMetadataBuilder $metadata,
    ) {
        $this->argumentSetName = $this->createArgumentSetName();
    }

    /**
     * @return non-empty-string
     */
    private function createArgumentSetName(): string
    {
        $prefix = new Randomizer()
            ->getBytes(8);

        return \bin2hex($prefix) . '_types';
    }

    /**
     * @param iterable<mixed, NamespaceNode> $nodes
     *
     * @return list<non-empty-string>
     */
    private function getTypeNames(iterable $nodes): array
    {
        $result = BuiltinTypes::getAll();

        foreach ($nodes as $namespace) {
            if (!$namespace->name === null) {
                continue;
            }

            foreach ($namespace->types as $type) {
                if (!$type instanceof OptionalNamedTypeInterface || $type->name === null) {
                    continue;
                }

                $result[] = $type->name;
            }
        }

        return \array_values(\array_unique($result));
    }

    /**
     * @param iterable<mixed, NamespaceNode> $nodes
     *
     * @return list<Arg>
     */
    private function getTypeNamesAsArgs(iterable $nodes): array
    {
        $result = [];

        foreach ($this->getTypeNames($nodes) as $name) {
            $result[] = new Arg(new String_($name));
        }

        return $result;
    }

    /**
     * @param iterable<mixed, NamespaceNode> $nodes
     *
     * @return list<PhpStatement>
     */
    public function generate(iterable $nodes): array
    {
        return [
            new Expression($this->metadata->createRegisterArgumentsSet(
                argumentSetName: $this->argumentSetName,
                args: $this->getTypeNamesAsArgs($nodes),
            )),
            new Expression($this->metadata->createExpectedArguments(
                class: \FFI::class,
                method: 'new',
                position: 0,
                argumentSetName: $this->argumentSetName,
            )),
            new Expression($this->metadata->createExpectedArguments(
                class: \FFI::class,
                method: 'cast',
                position: 0,
                argumentSetName: $this->argumentSetName,
            )),
            new Expression($this->metadata->createExpectedArguments(
                class: \FFI::class,
                method: 'type',
                position: 0,
                argumentSetName: $this->argumentSetName,
            )),
        ];
    }
}
