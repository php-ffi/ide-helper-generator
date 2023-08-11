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
    /**
     * List of builtin types and its core type alias.
     *
     * @var list<non-empty-string, non-empty-string>
     */
    private const BUILTIN_TYPES = [
        'void*' => 'void*',
        'bool' => 'bool',
        'float' => 'float',
        'double' => 'double',
        'long double' => 'double',
        'char' => 'char',
        'signed char' => 'int8_t',
        'unsigned char' => 'uint8_t',

        'short' => 'int16_t',
        'short int' => 'int16_t',
        'signed short' => 'int16_t',
        'short signed' => 'int16_t',
        'signed short int' => 'int16_t',
        'short signed int' => 'int16_t',
        'unsigned short' => 'uint16_t',
        'short unsigned' => 'uint16_t',
        'short unsigned int' => 'uint16_t',

        'int' => 'int32_t',
        'signed int' => 'int32_t',
        'unsigned int' => 'uint32_t',

        'long' => 'int32_t',
        'long int' => 'int32_t',
        'signed long' => 'int32_t',
        'long signed' => 'int32_t',
        'signed long int' => 'int32_t',
        'long signed int' => 'int32_t',
        'unsigned long' => 'uint32_t',
        'long unsigned' => 'uint32_t',
        'unsigned long int' => 'uint32_t',
        'long unsigned int' => 'uint32_t',

        'long long' => 'int64_t',
        'long long int' => 'int64_t',
        'signed long long' => 'int64_t',
        'long long signed' => 'int64_t',
        'signed long long int' => 'int64_t',
        'long long signed int' => 'int64_t',
        'unsigned long long' => 'uint64_t',
        'long long unsigned' => 'uint64_t',
        'unsigned long long int' => 'uint64_t',
        'long long unsigned int' => 'uint64_t',

        'intptr_t' => 'int64_t',
        'uintptr_t' => 'uint64_t',
        'size_t' => 'uint64_t',
        'ssize_t' => 'int64_t',
        'ptrdiff_t' => 'int64_t',
        'off_t' => 'int32_t',
        'va_list' => 'void*',
        '__builtin_va_list' => 'void*',
        '__gnuc_va_list' => 'void*',
        'int8_t' => 'int8_t',
        'uint8_t' => 'uint8_t',
        'int16_t' => 'int16_t',
        'uint16_t' => 'uint16_t',
        'int32_t' => 'int32_t',
        'uint32_t' => 'uint32_t',
        'int64_t' => 'int64_t',
        'uint64_t' => 'uint64_t',
    ];

    /**
     * @var array<non-empty-string, TypeInfo>
     */
    private array $builtin = [];

    public function __construct(
        private readonly NamingStrategyInterface $naming,
    ) {
    }

    /**
     * @return iterable<non-empty-string, non-empty-string>
     */
    public static function getBuiltinTypeNames(): iterable
    {
        return self::BUILTIN_TYPES;
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
            $terminal = $this->getTerminalType($type->type);

            $info->phpTypes = ['null', '\FFI\CData'];
            $info->docTypes = ['null'];

            $child = $this->get($ctx, $type->type);

            if (($childDocType = $child->getDocTypeAsString()) && $childDocType !== 'mixed') {
                switch (true) {
                    case $terminal instanceof FundamentalTypeNode:
                        $info->addDocType('\FFI\CData', 'object{cdata:' . $childDocType . '}');
                        break;
                    default:
                        $info->addDocType('\FFI\CData', 'array{' . $childDocType . '}');
                        break;
                }
            } else {
                $info->addDocType('\FFI\CData');
            }

            //
            // The "char*" is looks like a string
            //
            if ($terminal instanceof FundamentalTypeNode && $terminal->name === 'char') {
                $info->phpTypes = $info->docTypes = ['string', '\FFI\CData'];
            } elseif ($terminal instanceof FunctionTypeNode) {
                $info->phpTypes = ['\Closure', 'null'];
                $info->docTypes = ['FFI\CData', 'null', $childDocType];
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
    public function getIntDocBlock(int $size, bool $unsigned): string
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
