<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\CastXmlParserResult;

use FFI\Generator\Node\Location;

final class CastXmlFileStorage
{
    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private array $files = [];

    public function __construct(
        private readonly CastXmlDocument $document,
    ) {}

    /**
     * @return non-empty-string
     */
    private function createFileById(string $id): string
    {
        $node = $this->document->getElementById($id);

        $name = $node->getAttribute('name');

        if ($name === '') {
            return Location::DEFAULT_BUILTIN_FILE;
        }

        return $name;
    }

    public function getLocation(string $location): Location
    {
        $parts = \explode(':', $location);

        if (\count($parts) !== 2) {
            return new Location();
        }

        if (!\ctype_digit($parts[1])) {
            throw new \InvalidArgumentException(\sprintf('Location line must be numeric, got %s', $parts[1]));
        }

        return new Location(
            file: $this->getFileById($parts[0]),
            line: \max(Location::MIN_LINE, (int) $parts[1]),
        );
    }

    /**
     * @return non-empty-string
     */
    public function getFileById(string $id): string
    {
        if ($id === '') {
            throw new \InvalidArgumentException('File ID cannot be empty');
        }

        return $this->files[$id] ??= $this->createFileById($id);
    }
}
