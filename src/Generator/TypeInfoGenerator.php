<?php

declare(strict_types=1);

namespace FFI\Generator\Generator;

use FFI\Generator\NamingStrategyInterface;
use FFI\Generator\Node\GenericTypeInterface;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\Type\ArrayTypeNode;
use FFI\Generator\Node\Type\ConstTypeNode;
use FFI\Generator\Node\Type\EnumTypeNode;
use FFI\Generator\Node\Type\FunctionTypeNode;
use FFI\Generator\Node\Type\FundamentalTypeNode;
use FFI\Generator\Node\Type\PointerTypeNode;
use FFI\Generator\Node\Type\RecordTypeNode;
use FFI\Generator\Node\Type\TypeDefinitionNode;
use FFI\Generator\Node\Type\TypeInterface;

final class TypeInfoGenerator
{
    public function __construct(
        private readonly NamingStrategyInterface $naming,
    ) {
    }

    public function get(NamespaceNode $ctx, TypeInterface $type): TypeInfo
    {
        $result = new TypeInfo();

        $this->apply($ctx, $type, $result);

        return $result;
    }

    private function apply(NamespaceNode $ctx, TypeInterface $type, TypeInfo $info): void
    {
        //
        // In case of type is a pointer
        //
        if ($type instanceof PointerTypeNode) {
            $terminal = $this->getTerminalType($type->getOfType());

            $info->phpTypes = ['null', '\FFI\CData'];
            $info->docTypes = ['null'];

            $child = $this->get($ctx, $type->getOfType());

            if (($childDocType = $child->getDocTypeAsString()) && $childDocType !== 'mixed') {
                $info->addDocType('\FFI\CData<' . $childDocType . '>');
            } else {
                $info->addDocType('\FFI\CData');
            }

            //
            // The "char*" is looks like a string
            //
            if ($terminal instanceof FundamentalTypeNode && $terminal->name === 'char') {
                $info->phpTypes = $info->docTypes = ['string', '\FFI\CData'];
            }

            if (!$type->type instanceof RecordTypeNode) {
                return;
            }
        }

        if ($type instanceof TypeDefinitionNode) {
            if ($type->type instanceof EnumTypeNode) {
                $info->addPhpType('int');
                $info->addDocType($this->getIntDocBlock(
                    size: $type->type->size,
                    unsigned: false,
                ));
                $info->addDocType(\vsprintf('\%s::*', [
                    $this->naming->getName($type->name, $type->type)
                ]));

                foreach ($type->type->values as $value) {
                    $info->addExpectedValue(\vsprintf('\%s::%s', [
                        $this->naming->getName($type->name, $type->type),
                        $this->naming->getName($value->name, $value),
                    ]));
                }
            }

            if ($type->type instanceof RecordTypeNode) {
                $info->addPhpType('\FFI\CData', 'null');
                $info->addDocType('\\' . $this->naming->getName($type->name, $type->type));
            }
        }

        if ($type instanceof RecordTypeNode && $type->name === null) {
            $info->addPhpType('\FFI\CData', 'null');

            $fields = [];

            foreach ($type->fields as $field) {
                // Do not add anonymous fields
                if ($field->name === null) {
                    continue;
                }

                $fieldInfo = $this->get($ctx, $field->type);

                $fields[] = \sprintf('%s:%s', $field->name, $fieldInfo->getDocTypeAsString() ?: 'mixed');
            }

            $info->addDocType('null', \sprintf('object{%s}', \implode(', ', $fields)));
        }

        if ($type instanceof FundamentalTypeNode) {
            switch ($type->name) {
                case 'void':
                    $info->addType('void');
                    break;
                case 'bool':
                    $info->addType('bool');
                    break;
                case 'float':
                case 'double':
                case 'long double':
                    $info->addType('float');
                    break;
                case 'char':
                    $info->addType('string');
                    break;
                case 'unsigned char':
                    $info->addPhpType('int');
                    $info->addDocType('int<0, 255>');
                    break;
                case 'signed char':
                    $info->addPhpType('int');
                    $info->addDocType('int<-128, 127>');
                    break;
                default:
                    $info->addPhpType('int');
                    $info->addDocType($this->getIntDocBlock(
                        size: $type->size,
                        unsigned: \str_contains($type->name, 'unsigned'),
                    ));
                    break;
            }
        }

        if ($type instanceof FunctionTypeNode) {
            $functionReturnTypeInfo = $this->get($ctx, $type->returns);

            $arguments = [];

            foreach ($type->arguments as $argument) {
                $arguments[] = $this->get($ctx, $argument->type)
                    ->getDocTypeAsString()
                    . ($argument->variadic ? '...' : '')
                ;
            }

            $info->addPhpType('\\Closure');
            $info->addDocType(\vsprintf('callable(%s):(%s)', [
                \implode(', ', $arguments),
                $functionReturnTypeInfo->getDocTypeAsString(),
            ]));
        }

        if ($type instanceof ArrayTypeNode) {
            $child = $this->get($ctx, $type->getOfType());

            $info->phpTypes = ['array'];
            $info->docTypes = ['list<' . ($child->getDocTypeAsString() ?: 'mixed') . '>'];

            return;
        }

        if ($type instanceof ConstTypeNode) {
            $info->const = true;
        }

        if ($type instanceof GenericTypeInterface) {
            $this->apply($ctx, $type->getOfType(), $info);
        }
    }

    /**
     * @param int<0, max> $size
     * @return non-empty-string
     */
    private function getIntDocBlock(int $size, bool $unsigned): string
    {
        $bounds = $this->getIntBounds($size, $unsigned);

        /** @var non-empty-string */
        return \vsprintf('int<%s, %s>', $bounds);
    }

    /**
     * @param int<0, max> $size
     * @return array{non-empty-string, non-empty-string}
     */
    private function getIntBounds(int $size, bool $unsigned): array
    {
        $bits = \PHP_INT_SIZE * 8;

        if ($unsigned) {
            return ['0', $size >= $bits ? 'max' : (string)(2 ** $size)];
        }

        return [
            $size >= $bits ? 'min' : (string)(2 ** ($size - 1) * -1),
            $size >= $bits ? 'max' : (string)(2 ** ($size - 1) - 1),
        ];
    }

    protected function getTerminalType(TypeInterface $type, bool $skipPointers = false): TypeInterface
    {
        if ($skipPointers === false && $type instanceof PointerTypeNode) {
            return $type;
        }

        if ($type instanceof GenericTypeInterface) {
            return $this->getTerminalType($type->getOfType());
        }

        return $type;
    }
}
