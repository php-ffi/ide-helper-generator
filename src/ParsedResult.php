<?php

declare(strict_types=1);

namespace FFI\Generator;

use FFI\Generator\Node\FunctionNode;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\Type\TypeInterface;
use FFI\Generator\Node\VariableNode;
use FFI\Generator\Printer\PrinterInterface;

final class ParsedResult
{
    /**
     * @var iterable<mixed, NamespaceNode>
     */
    public iterable $nodes {
        /**
         * @return array<array-key, NamespaceNode>
         */
        get {
            if (\is_array($this->nodes)) {
                return $this->nodes;
            }

            return $this->nodes = \iterator_to_array($this->nodes, false);
        }
    }

    /**
     * @param iterable<mixed, NamespaceNode> $nodes
     */
    public function __construct(iterable $nodes)
    {
        $this->nodes = $nodes;
    }

    /**
     * @param \Closure(TypeInterface|FunctionNode|VariableNode):bool $filter
     */
    public function only(\Closure $filter): self
    {
        foreach ($this->nodes as $namespace) {
            $namespace->types = \array_filter($namespace->types, $filter);
            $namespace->functions = \array_filter($namespace->functions, $filter);
            $namespace->variables = \array_filter($namespace->variables, $filter);
        }

        return $this;
    }

    /**
     * @template TArgResult of GeneratedResult
     *
     * @param PrinterInterface<TArgResult> $printer
     * @return TArgResult
     */
    public function generate(PrinterInterface $printer): GeneratedResult
    {
        return $printer->print($this->nodes);
    }
}
