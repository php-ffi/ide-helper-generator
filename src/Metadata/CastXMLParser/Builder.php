<?php

declare(strict_types=1);

namespace FFI\Generator\Metadata\CastXMLParser;

use FFI\Generator\Node\FunctionArgumentNode;
use FFI\Generator\Node\Type\ArrayTypeNode;
use FFI\Generator\Node\Type\ClassTypeNode;
use FFI\Generator\Node\Type\ConstTypeNode;
use FFI\Generator\Node\Type\EnumTypeNode;
use FFI\Generator\Node\FileNode;
use FFI\Generator\Node\FunctionTypeArgumentNode;
use FFI\Generator\Node\Type\FunctionTypeNode;
use FFI\Generator\Node\Type\FundamentalTypeNode;
use FFI\Generator\Node\Type\GenericType;
use FFI\Generator\Node\LocationNode;
use FFI\Generator\Node\FunctionNode;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\Type\PointerTypeNode;
use FFI\Generator\Node\RecordFieldNode;
use FFI\Generator\Node\Type\RecordTypeNode;
use FFI\Generator\Node\Type\RestrictType;
use FFI\Generator\Node\Type\StructTypeNode;
use FFI\Generator\Node\Type\TypeDefinitionNode;
use FFI\Generator\Node\Type\TypeInterface;
use FFI\Generator\Node\Type\UnimplementedDeclarationTypeNode;
use FFI\Generator\Node\Type\UnimplementedTypeNode;
use FFI\Generator\Node\Type\UnionTypeNode;
use FFI\Generator\Node\UnknownTypeNode;
use FFI\Generator\Node\Type\VolatileTypeNode;

/**
 * @template-implements \IteratorAggregate<array-key, NamespaceNode>
 *
 * @internal This is an internal library class, please do not use it in your code.
 * @psalm-internal FFI\Generator\Metadata
 *
 * @psalm-suppress PropertyTypeCoercion
 * @psalm-suppress ArgumentTypeCoercion
 * @psalm-suppress PossiblyInvalidArrayOffset
 * @psalm-suppress InvalidPropertyAssignmentValue
 */
final class Builder implements \IteratorAggregate
{
    /**
     * @var \DOMDocument
     */
    private \DOMDocument $dom;

    /**
     * @var array<non-empty-string, TypeInterface>
     */
    private array $types = [];

    /**
     * @var array<non-empty-string, FileNode>
     */
    private array $files = [];

    /**
     * @param \DOMDocument $dom
     */
    public function __construct(\DOMDocument $dom)
    {
        if (!$dom->firstChild || $dom->firstChild->nodeName !== 'CastXML') {
            throw new \InvalidArgumentException('Passed DOMDocument is not looks like of CastXML file');
        }

        $this->dom = $dom;
    }

    private function getTypeDefinition(\DOMElement $el): TypeDefinitionNode
    {
        $typedef = $this->types[$el->getAttribute('id')] = new TypeDefinitionNode(
            name: $el->getAttribute('name'),
        );

        $typedef->type = $this->getTypeById(
            id: $el->getAttribute('type'),
        );

        $typedef->location = $this->getLocationBySignature(
            id: $el->getAttribute('location'),
        );

        return $typedef;
    }

    private function getEnumeration(\DOMElement $el): EnumTypeNode
    {
        $enum = $this->types[$el->getAttribute('id')] = new EnumTypeNode(
            name: $el->getAttribute('name') ?: null,
            size: (int)$el->getAttribute('size'),
            align: (int)$el->getAttribute('align'),
        );

        /** @var \DOMElement $value */
        foreach ($el->childNodes as $value) {
            if ($value->nodeName !== 'EnumValue') {
                continue;
            }

            $enum->values[] = new \FFI\Generator\Node\EnumValueNode(
                name: $value->getAttribute('name'),
                value: (int)$value->getAttribute('init'),
            );
        }

        $enum->location = $this->getLocationBySignature(
            id: $el->getAttribute('location'),
        );

        return $enum;
    }

    private function getFunction(\DOMElement $el): FunctionNode
    {
        $function = new FunctionNode($el->getAttribute('name') ?: null);

        /** @var \DOMElement $member */
        foreach ($el->childNodes as $member) {
            switch ($member->nodeName) {
                case 'Argument':
                    $argument = new FunctionArgumentNode($member->getAttribute('name') ?: null);

                    $argument->type = $member->getAttribute('original_type')
                        ? $this->getTypeById($member->getAttribute('original_type'))
                        : $this->getTypeById($member->getAttribute('type'))
                    ;

                    $function->arguments[] = $argument;
                    break;

                case 'Ellipsis':
                    if (!isset($argument) || !$argument instanceof FunctionArgumentNode) {
                        throw new \LogicException('Cannot make variadic argument of empty function');
                    }

                    $argument->variadic = true;
                    break;
            }
        }

        $function->returns = $this->getTypeById(
            id: $el->getAttribute('returns'),
        );

        $function->location = $this->getLocationBySignature(
            id: $el->getAttribute('location'),
        );

        return $function;
    }

    private function getFunctionType(\DOMElement $el): FunctionTypeNode
    {
        $function = $this->types[$el->getAttribute('id')] = new FunctionTypeNode();

        $function->returns = $this->getTypeById(
            id: $el->getAttribute('returns'),
        );

        /** @var \DOMElement $member */
        foreach ($el->childNodes as $member) {
            switch ($member->nodeName) {
                case 'Argument':
                    $argument = new FunctionTypeArgumentNode();

                    $argument->type = $member->getAttribute('original_type')
                        ? $this->getTypeById($member->getAttribute('original_type'))
                        : $this->getTypeById($member->getAttribute('type'))
                    ;

                    $function->arguments[] = $argument;
                    break;

                case 'Ellipsis':
                    if (!isset($argument) || !$argument instanceof FunctionTypeArgumentNode) {
                        throw new \LogicException('Cannot make variadic argument of empty function');
                    }

                    $argument->variadic = true;
                    break;
            }
        }

        return $function;
    }

    private function getStruct(\DOMElement $el): StructTypeNode
    {
        $struct = $this->types[$el->getAttribute('id')] = new StructTypeNode(
            name: $el->getAttribute('name') ?: null,
        );

        return $this->fillRecord($el, $struct);
    }

    private function getUnion(\DOMElement $el): UnionTypeNode
    {
        $union = $this->types[$el->getAttribute('id')] = new UnionTypeNode(
            name: $el->getAttribute('name') ?: null,
        );

        return $this->fillRecord($el, $union);
    }

    private function getClass(\DOMElement $el): ClassTypeNode
    {
        $union = $this->types[$el->getAttribute('id')] = new ClassTypeNode(
            name: $el->getAttribute('name') ?: null,
        );

        return $this->fillRecord($el, $union);
    }

    /**
     * @template TRecordType of RecordTypeNode
     *
     * @param TRecordType $record
     *
     * @return TRecordType
     */
    private function fillRecord(\DOMElement $el, RecordTypeNode $record): RecordTypeNode
    {
        $record->location = $this->getLocationBySignature(
            id: $el->getAttribute('location'),
        );

        if ($members = $el->getAttribute('members')) {
            foreach ($this->getElementsByMemberIds($members) as $member) {
                if ($member->nodeName !== 'Field') {
                    continue;
                }

                $field = $this->types[$member->getAttribute('id')] = new RecordFieldNode(
                    name: $member->getAttribute('name') ?: null,
                );

                $field->type = $this->getTypeById($member->getAttribute('type'));

                $record->fields[] = $field;
            }
        }

        return $record;
    }

    private function getFundamentalType(\DOMElement $el): FundamentalTypeNode
    {
        return $this->types[$el->getAttribute('id')] = new FundamentalTypeNode(
            name: $el->getAttribute('name'),
            size: (int)$el->getAttribute('size'),
            align: (int)$el->getAttribute('align'),
        );
    }

    private function getPointerType(\DOMElement $el): PointerTypeNode
    {
        return $this->types[$el->getAttribute('id')] = new PointerTypeNode(
            type: $this->getTypeById($el->getAttribute('type')),
        );
    }

    private function getArrayType(\DOMElement $el): ArrayTypeNode
    {
        return $this->types[$el->getAttribute('id')] = new ArrayTypeNode(
            type: $this->getTypeById($el->getAttribute('type')),
        );
    }

    private function getCvQualifiedType(\DOMElement $el): GenericType
    {
        $type = $this->getTypeById($el->getAttribute('type'));

        assert($type instanceof GenericType);

        if ($el->getAttribute('restrict') === '1') {
            $type = new RestrictType($type);
        }

        if ($el->getAttribute('volatile') === '1') {
            $type = new VolatileTypeNode($type);
        }

        if ($el->getAttribute('const') === '1') {
            $type = new ConstTypeNode($type);
        }

        return $type;
    }

    private function getUnimplementedType(\DOMElement $el): UnimplementedTypeNode
    {
        if ($kind = $el->getAttribute('kind')) {
            return new UnimplementedDeclarationTypeNode($kind);
        }

        return new UnimplementedTypeNode($el->getAttribute('type_class'));
    }

    private function getNamespace(\DOMElement $el): NamespaceNode
    {
        $namespace = new NamespaceNode($el->getAttribute('name'));

        foreach ($this->getElementsByMemberIds($el->getAttribute('members')) as $node) {
            if ($node->nodeName === 'Function') {
                $function = $this->getFunction($node);

                // Skip anonymous global functions
                if ($function->name === null) {
                    continue;
                }

                $namespace->functions[$function->name] = $function;
                continue;
            }

            if ($node->nodeName === 'Typedef') {
                $type = $this->getTypeDefinition($node);

                $namespace->types[$type->name] = $type;
            }
        }

        return $namespace;
    }

    private function getType(\DOMElement $el): TypeInterface
    {
        return $this->types[$id = $el->getAttribute('id')] ?? match ($el->nodeName) {
            'Typedef' => $this->getTypeDefinition($el),
            'Struct' => $this->getStruct($el),
            'Union' => $this->getUnion($el),
            'Class' => $this->getClass($el),
            'FundamentalType' => $this->getFundamentalType($el),
            'ArrayType' => $this->getArrayType($el),
            'CvQualifiedType' => $this->getCvQualifiedType($el),
            'PointerType' => $this->getPointerType($el),
            'Enumeration' => $this->getEnumeration($el),
            'FunctionType' => $this->getFunctionType($el),
            'Unimplemented' => $this->getUnimplementedType($el),
            'ElaboratedType' => $this->getTypeById($el->getAttribute('type')),
            default => $this->types[$id] = new UnknownTypeNode($el->nodeName),
        };
    }

    /**
     * @param non-empty-string $id
     */
    private function getTypeById(string $id): TypeInterface
    {
        return $this->getType($this->getElementById($id));
    }

    private function getLocationBySignature(string $id): LocationNode
    {
        [$fileId, $line] = \explode(':', $id);

        return new LocationNode(
            file: $this->getFileById($fileId),
            line: (int)$line,
        );
    }

    private function getFile(\DOMElement $el): FileNode
    {
        return $this->files[$el->getAttribute('id')] ??= new FileNode(
            name: $el->getAttribute('name'),
        );
    }

    private function getFileById(string $id): FileNode
    {
        return $this->getFile($this->getElementById($id));
    }

    /**
     * @param non-empty-string $id
     * @return \DOMElement
     */
    private function getElementById(string $id): \DOMElement
    {
        $result = $this->findElementById($id);

        if (!$result instanceof \DOMElement) {
            throw new \LogicException('DOMElement(id="' . $id . '") could not be found');
        }

        return $result;
    }

    /**
     * @param string $ids
     * @return iterable<array-key, \DOMElement>
     */
    private function getElementsByMemberIds(string $ids): iterable
    {
        foreach (\explode(' ', $ids) as $id) {
            yield $this->getElementById($id);
        }
    }

    /**
     * @param non-empty-string $id
     * @return \DOMElement|null
     */
    private function findElementById(string $id): ?\DOMElement
    {
        return $this->dom->getElementById($id);
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->dom->getElementsByTagName('Namespace') as $namespace) {
            yield $this->getNamespace($namespace);
        }
    }
}
