<?php

declare(strict_types=1);

namespace FFI\Generator\Printer\PhpStormMetadataPrinter\Generator;

use FFI\CData;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\Type\ConstTypeNode;
use FFI\Generator\Node\Type\GenericTypeInterface;
use FFI\Generator\Node\Type\StructTypeNode;
use FFI\Generator\Node\Type\TypeDefinitionTypeNode;
use FFI\Generator\Node\Type\TypeInterface;
use FFI\Generator\Printer\PhpStormMetadataPrinter\PhpStormMetadataBuilder;
use FFI\Generator\Printer\PhpStormMetadataPrinter\PhpStormMetadataCreateInfo;
use FFI\Generator\TypeResolver\Direction;
use FFI\Generator\TypeResolver\TypeResolverInterface;
use PhpParser\Comment;
use PhpParser\Modifiers;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt as PhpStatement;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use TypeLang\Printer\PrinterInterface as TypePrinterInterface;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Generator\Printer
 */
final readonly class PhpStormMetadataStructAutocompleteGenerator
{
    private const string PROPERTY_TEMPLATE = <<<'PHPDOC'
        /**
         * @var %s
         */
        PHPDOC;

    private const string CLASS_TEMPLATE = <<<'PHPDOC'
        /**
         * @internal Internal class to ensure precise type inference
         * @template-extends \FFI\CData<void>
         */
        PHPDOC;

    public function __construct(
        private PhpStormMetadataCreateInfo $config,
        private PhpStormMetadataBuilder $metadata,
        private TypeResolverInterface $typeResolver,
        private TypePrinterInterface $phpTypePrinter,
        private TypePrinterInterface $phpdocTypePrinter,
    ) {}

    /**
     * @return array{non-empty-string, non-empty-string}
     */
    private function print(TypeInterface $type, Direction $direction): array
    {
        $statement = $this->typeResolver->resolve($type, $direction);

        return [
            $this->phpTypePrinter->print($statement),
            $this->phpdocTypePrinter->print($statement),
        ];
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $type
     */
    private function createProperty(string $name, string $type, string $phpdocType, bool $isReadonly): Property
    {
        $comments = [];

        if ($phpdocType !== $type) {
            $comments[] = new Comment(\sprintf(self::PROPERTY_TEMPLATE, $phpdocType));
        }

        $flags = Modifiers::PUBLIC;

        if ($isReadonly) {
            $flags |= Modifiers::READONLY;
        }

        return new Property(
            flags: $flags,
            props: [new PropertyItem($name)],
            attributes: ['comments' => $comments],
            type: new Name($type),
        );
    }

    private function isReadonly(TypeInterface $type): bool
    {
        if ($type instanceof ConstTypeNode) {
            return true;
        }

        if ($type instanceof GenericTypeInterface) {
            return $this->isReadonly($type->type);
        }

        return false;
    }

    private function getStructDefinition(string $name, StructTypeNode $record): Class_
    {
        /** @var list<Property> $properties */
        $properties = [];

        foreach ($record->fields as $field) {
            if ($field->name === null) {
                continue;
            }

            [$phpType, $phpdocType] = $this->print($field->type, Direction::Input);

            $properties[] = $this->createProperty(
                name: $field->name,
                type: $phpType,
                phpdocType: $phpdocType,
                isReadonly: $this->isReadonly($field->type),
            );
        }

        return new Class_(
            name: $name,
            subNodes: [
                'extends' => new FullyQualified(CData::class),
                'flags' => Modifiers::FINAL,
                'stmts' => $properties,
            ],
            attributes: [
                'comments' => [new Comment(self::CLASS_TEMPLATE)],
            ],
        );
    }

    /**
     * @param iterable<mixed, NamespaceNode> $nodes
     *
     * @return list<Class_>
     */
    private function getStructDefinitions(iterable $nodes): array
    {
        $definitions = [];

        foreach ($nodes as $namespace) {
            /** @var TypeDefinitionTypeNode<StructTypeNode> $struct */
            foreach ($namespace->structures as $struct) {
                $definitions[] = $this->getStructDefinition($struct->name, $struct->type);
            }
        }

        return $definitions;
    }

    /**
     * @param iterable<mixed, NamespaceNode> $nodes
     * @return array<string, string>
     */
    private function getStructNameMap(iterable $nodes): array
    {
        $result = [];

        foreach ($nodes as $namespace) {
            if (!$namespace->name === null) {
                continue;
            }

            foreach ($namespace->structures as $struct) {
                $result[$struct->name] = '\\PHPSTORM_META\\' . $struct->name;
            }
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
            ...$this->getStructDefinitions($nodes),
            new Expression($this->metadata->createOverride(
                class: \FFI::class,
                method: 'new',
                position: 0,
                value: $this->metadata->createMap($this->getStructNameMap($nodes)),
            )),
        ];
    }
}
