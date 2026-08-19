<?php

declare(strict_types=1);

namespace FFI\Generator\Printer;

use FFI\Generator\GeneratedResult;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Printer\PhpStormMetadataPrinter\Generator\PhpStormMetadataStructAutocompleteGenerator;
use FFI\Generator\Printer\PhpStormMetadataPrinter\Generator\PhpStormMetadataTypesAutocompleteGenerator;
use FFI\Generator\Printer\PhpStormMetadataPrinter\PhpStormMetadataBuilder;
use FFI\Generator\Printer\PhpStormMetadataPrinter\PhpStormMetadataCreateInfo;
use FFI\Generator\TypeResolver\Factory\TypeFactoryInterface;
use PhpParser\Comment;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt as PhpStatement;
use PhpParser\Node\Stmt\Namespace_ as PhpNamespaceStatement;
use PhpParser\PrettyPrinter as PhpPrinterInterface;
use TypeLang\Printer\PrinterInterface as TypePrinterInterface;

final readonly class PhpStormMetadataPrinter extends Printer
{
    public const string DEFAULT_TARGET_CLASS = \FFI::class;

    private PhpStormMetadataBuilder $metadata;

    public function __construct(
        private PhpStormMetadataCreateInfo $config = new PhpStormMetadataCreateInfo(),
        ?TypeFactoryInterface $typeFactory = null,
        ?PhpPrinterInterface $phpPrinter = null,
        ?TypePrinterInterface $phpTypePrinter = null,
        ?TypePrinterInterface $phpdocTypePrinter = null,
    ) {
        $this->metadata = new PhpStormMetadataBuilder();

        parent::__construct(
            typeFactory: $typeFactory,
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
        $comments = [];

        if ($this->config->comment !== null) {
            $comments[] = new Comment($this->config->comment);
        }

        return new GeneratedResult(
            result: $this->phpPrinter->prettyPrintFile([
                new PhpNamespaceStatement(
                    name: new Name($this->config->namespace),
                    stmts: $statements,
                    attributes: [
                        'kind' => PhpNamespaceStatement::KIND_BRACED,
                        'comments' => $comments,
                    ],
                ),
            ]),
        );
    }

    /**
     * @param iterable<array-key, NamespaceNode> $nodes
     *
     * @return list<PhpStatement>
     */
    private function getTypesAutocompleteStatements(iterable $nodes): array
    {
        $generator = new PhpStormMetadataTypesAutocompleteGenerator(
            config: $this->config,
            metadata: $this->metadata,
        );

        return $generator->generate($nodes);
    }

    /**
     * @param iterable<array-key, NamespaceNode> $nodes
     *
     * @return list<PhpStatement>
     */
    private function getStructStatements(iterable $nodes): array
    {
        $generator = new PhpStormMetadataStructAutocompleteGenerator(
            config: $this->config,
            metadata: $this->metadata,
            typeFactory: $this->typeFactory,
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
