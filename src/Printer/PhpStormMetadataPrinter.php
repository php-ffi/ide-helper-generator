<?php

declare(strict_types=1);

namespace FFI\Generator\Printer;

use FFI\Generator\GeneratedResult;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Printer\PhpStormMetadataPrinter\Generator\PhpStormMetadataStructAutocompleteGenerator;
use FFI\Generator\Printer\PhpStormMetadataPrinter\Generator\PhpStormMetadataTypesAutocompleteGenerator;
use FFI\Generator\Printer\PhpStormMetadataPrinter\PhpStormMetadataBuilder;
use FFI\Generator\TypeResolver\TypeResolverInterface;
use PhpParser\Comment;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt as PhpStatement;
use PhpParser\Node\Stmt\Namespace_ as PhpNamespaceStatement;
use PhpParser\PrettyPrinter as PhpPrinterInterface;
use TypeLang\Printer\PrinterInterface as TypePrinterInterface;

final readonly class PhpStormMetadataPrinter extends Printer
{
    private const string PHPSTORM_META_HEADER = <<<'COMMENT'
        /**
         * A helper file for FFI, to provide autocomplete information to your IDE
         *
         * This file should not be included in your code, only analyzed by your IDE!
         *
         * @link https://github.com/php-ffi/generator
         *
         * @psalm-suppress all
         */
        COMMENT;

    public const string DEFAULT_TARGET_CLASS = \FFI::class;

    private PhpStormMetadataBuilder $metadata;

    public function __construct(
        /**
         * @var non-empty-string
         */
        private string $namespace = 'PHPSTORM_META',
        ?TypeResolverInterface $typeResolver = null,
        ?PhpPrinterInterface $phpPrinter = null,
        ?TypePrinterInterface $phpTypePrinter = null,
        ?TypePrinterInterface $phpdocTypePrinter = null,
    ) {
        $this->metadata = new PhpStormMetadataBuilder();

        parent::__construct(
            typeResolver: $typeResolver,
            phpPrinter: $phpPrinter,
            phpTypePrinter: $phpTypePrinter,
            phpdocTypePrinter: $phpdocTypePrinter,
        );
    }

    /**
     * @param array<array-key, PhpStatement> $statements
     */
    private function create(array $statements): GeneratedResult
    {
        return new GeneratedResult(
            result: $this->phpPrinter->prettyPrintFile([
                new PhpNamespaceStatement(
                    name: new Name($this->namespace),
                    stmts: $statements,
                    attributes: [
                        'kind' => PhpNamespaceStatement::KIND_BRACED,
                        'comments' => [
                            new Comment('// @formatter:off'),
                            new Comment('// phpcs:ignoreFile'),
                            new Comment(self::PHPSTORM_META_HEADER),
                        ],
                    ],
                ),
            ]),
        );
    }

    /**
     * @param iterable<array-key, NamespaceNode> $nodes
     * @return list<PhpStatement>
     */
    private function getTypesAutocompleteStatements(iterable $nodes): array
    {
        $generator = new PhpStormMetadataTypesAutocompleteGenerator(
            metadata: $this->metadata,
        );

        return $generator->generate($nodes);
    }

    /**
     * @param iterable<array-key, NamespaceNode> $nodes
     * @return list<PhpStatement>
     */
    private function getStructStatements(iterable $nodes): array
    {
        $generator = new PhpStormMetadataStructAutocompleteGenerator(
            metadata: $this->metadata,
            typeResolver: $this->typeResolver,
            phpTypePrinter: $this->phpTypePrinter,
            phpdocTypePrinter: $this->phpdocTypePrinter,
        );

        return $generator->generate($nodes);
    }

    public function print(iterable $nodes, int $phpVersion = self::DEFAULT_PHP_VERSION): GeneratedResult
    {
        return $this->create([
            ...$this->getTypesAutocompleteStatements($nodes),
            ...$this->getStructStatements($nodes),
        ]);
    }
}
