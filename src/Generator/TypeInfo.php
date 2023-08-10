<?php

declare(strict_types=1);

namespace FFI\Generator\Generator;

/**
 * @internal This is an internal library class, please do not use it in your code.
 * @psalm-internal FFI\Generator
 */
final class TypeInfo
{
    /**
     * @param list<non-empty-string> $phpTypes
     * @param list<non-empty-string> $docTypes
     * @param list<non-empty-string> $expectedValues
     */
    public function __construct(
        public array $phpTypes = [],
        public array $docTypes = [],
        public bool $const = false,
        public array $expectedValues = [],
    ) {}

    public function addType(string ...$types): void
    {
        $this->addPhpType(...$types);
        $this->addDocType(...$types);
    }

    public function addExpectedValue(string ...$values): void
    {
        foreach ($values as $value) {
            if (($value = \trim($value)) === '') {
                continue;
            }

            $this->phpTypes[] = $value;
        }
    }

    public function addPhpType(string ...$types): void
    {
        foreach ($types as $type) {
            if (($type = \trim($type)) === '') {
                continue;
            }

            $this->phpTypes[] = $type;
        }
    }

    public function addDocType(string ...$types): void
    {
        foreach ($types as $type) {
            if (($type = \trim($type)) === '') {
                continue;
            }

            $this->docTypes[] = $type;
        }
    }

    public function getPhpTypeAsString(): string
    {
        $types = \array_values(\array_unique($this->phpTypes));

        if (\count($types) > 1 && \in_array('void', $types, true)) {
            $types = \array_filter($types, static fn(string $type): bool => $type !== 'void');
        }

        // In case of type is "null|T"
        if (\count($types) === 2 && \in_array('null', $types, true)) {
            // Replace to "?T"
            $type = \array_filter($types, static fn(string $type): bool => $type !== 'null');

            return '?' . \reset($type);
        }

        return \implode('|', $types) ?: 'mixed';
    }

    public function getDocTypeAsString(): string
    {
        $types = \array_values(\array_unique($this->docTypes));

        if ($types === $this->phpTypes) {
            return 'mixed';
        }

        return \implode('|', $types) ?: 'mixed';
    }
}
