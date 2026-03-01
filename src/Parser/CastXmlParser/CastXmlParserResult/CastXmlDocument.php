<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\CastXmlParserResult;

final readonly class CastXmlDocument
{
    public function __construct(
        private \DOMDocument $document,
    ) {
        if ($document->firstChild?->nodeName !== 'CastXML') {
            throw new \InvalidArgumentException('Passed DOMDocument is not looks like of CastXML file');
        }
    }

    public function findElementById(string $id): ?\DOMElement
    {
        if ($id === '') {
            throw new \InvalidArgumentException('An element ID cannot be empty');
        }

        return $this->document->getElementById($id);
    }

    public function getElementById(string $id): \DOMElement
    {
        return $this->findElementById($id)
            ?? throw new \LogicException(\sprintf('DOMElement with id="%s" not found', $id));
    }

    /**
     * @param list<string> $ids
     * @return list<\DOMElement>
     */
    public function getElementsByIdList(array $ids): array
    {
        $result = [];

        foreach ($ids as $id) {
            $result[] = $this->getElementById($id);
        }

        return $result;
    }

    /**
     * @return iterable<array-key, \DOMElement>
     */
    public function getElementsByIdListString(string $idsString): iterable
    {
        return $this->getElementsByIdList(\explode(' ', $idsString));
    }

    /**
     * @param non-empty-string $name
     * @return iterable<array-key, \DOMElement>
     */
    public function getElementsByTagName(string $name): iterable
    {
        return $this->document->getElementsByTagName($name);
    }
}
