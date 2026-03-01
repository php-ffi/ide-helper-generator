<?php

declare(strict_types=1);

namespace FFI\Generator\Parser;

use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Parser\CastXmlParser\CastXmlGenerator;
use FFI\Generator\Parser\CastXmlParser\CastXmlParserResult;

final readonly class CastXmlParser implements ParserInterface
{
    /**
     * @var non-empty-string
     */
    private const string SCHEMA_XSD = __DIR__ . '/../../resources/castxml.xsd';

    /**
     * @var non-empty-string
     */
    private const string DEFAULT_CHARSET = 'UTF-8';

    public CastXmlGenerator $generator;

    /**
     * @param non-empty-string $castXmlBinary
     * @param non-empty-string|null $tempDirectory
     */
    public function __construct(
        string $castXmlBinary = CastXmlGenerator::DEFAULT_CAST_XML_BINARY,
        ?string $tempDirectory = CastXmlGenerator::DEFAULT_TEMP_DIRECTORY,
    ) {
        $this->generator = new CastXmlGenerator($castXmlBinary, $tempDirectory);
    }

    private function createDOMDocument(): \DOMDocument
    {
        if (!\class_exists(\DOMDocument::class)) {
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
     * @param non-empty-string $pathname
     */
    private function createDOMDocumentFromFile(string $pathname): \DOMDocument
    {
        $dom = $this->createDOMDocument();

        \error_clear_last();

        @$dom->load($pathname, \LIBXML_NONET);

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
     * @param non-empty-string $pathname
     *
     * @return iterable<array-key, NamespaceNode>
     */
    public function parse(string $pathname): iterable
    {
        if (!\is_file($pathname) || !\is_readable($pathname)) {
            throw new \InvalidArgumentException(
                message: \sprintf('File "%s" not found or not readable', $pathname),
            );
        }

        $result = $this->generator->generate($pathname);

        return new CastXmlParserResult(
            dom: $this->createDOMDocumentFromFile(
                pathname: $result->pathname,
            ),
        );
    }
}
