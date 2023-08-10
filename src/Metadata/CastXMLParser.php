<?php

declare(strict_types=1);

namespace FFI\Generator\Metadata;

use FFI\Generator\Metadata\CastXMLParser\Builder;
use FFI\Generator\Node\NamespaceNode;

final class CastXMLParser implements ParserInterface
{
    /**
     * @var non-empty-string
     */
    private const SCHEMA_XSD = __DIR__ . '/../../resources/castxml.xsd';

    /**
     * @var non-empty-string
     */
    private const DEFAULT_CHARSET = 'UTF-8';

    private function createDOMDocument(): \DOMDocument
    {
        if (! \class_exists(\DOMDocument::class)) {
            throw new \LogicException('ext-dom extension not available');
        }

        $internalErrors = \libxml_use_internal_errors(true);

        try {
            $dom = new \DOMDocument('1.0', self::DEFAULT_CHARSET);
            $dom->validateOnParse = true;
            $dom->xmlStandalone = true;
            $dom->strictErrorChecking = true;

            return $dom;
        } finally {
            \libxml_use_internal_errors($internalErrors);
        }
    }

    /**
     * @psalm-taint-sink file $filename
     * @param non-empty-string $filename
     */
    private function createDOMDocumentFromFile(string $filename): \DOMDocument
    {
        $dom = $this->createDOMDocument();

        \error_clear_last();

        @$dom->load($filename, \LIBXML_NONET);

        if ($error = \error_get_last()) {
            throw new \InvalidArgumentException(
                $error['message'] ?? 'Error while loading XML file',
                $error['type'] ?? 0,
            );
        }

        $dom->schemaValidate(self::SCHEMA_XSD);

        return $dom;
    }

    /**
     * @psalm-taint-sink file $filename
     * @param non-empty-string $filename
     * @return iterable<array-key, NamespaceNode>
     */
    public function parse(string $filename): iterable
    {
        if (!\is_file($filename) || !\is_readable($filename)) {
            throw new \InvalidArgumentException(
                message: \sprintf('File "%s" not found or not readable', $filename),
            );
        }

        return new Builder($this->createDOMDocumentFromFile(
            filename: $filename,
        ));
    }
}
