<?php

declare(strict_types=1);

namespace FFI\Generator;

use FFI\Generator\Node\DefinitionInterface;
use FFI\Generator\Node\NodeInterface;
use FFI\Generator\Parser\CastXmlParser;
use FFI\Generator\Parser\ParserInterface;
use FFI\Generator\Printer\PrinterInterface;

final readonly class CodeGenerator
{
    public function __construct(
        private ParserInterface $parser = new CastXmlParser(),
    ) {}

    /**
     * @param non-empty-string $pathname
     */
    public function parse(string $pathname): ParsedResult
    {
        $ast = $this->parser->parse($pathname);

        $directory = \dirname($pathname);

        return new ParsedResult($ast)
            ->only(static function (NodeInterface $node) use ($directory): bool {
                if ($node instanceof DefinitionInterface) {
                    return $node->location->in([$directory]);
                }

                return false;
            });
    }

    /**
     * @template TArgResult of GeneratedResult
     * @param non-empty-string $pathname
     * @param PrinterInterface<TArgResult> $printer
     * @return TArgResult
     */
    public function parseAndGenerate(string $pathname, PrinterInterface $printer): GeneratedResult
    {
        $parsed = $this->parse($pathname);

        return $parsed->generate($printer);
    }
}
