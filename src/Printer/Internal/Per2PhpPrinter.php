<?php

declare(strict_types=1);

namespace FFI\Generator\Printer\Internal;

use PhpParser\Node\Stmt\Declare_;
use PhpParser\PrettyPrinter\Standard;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal FFI\Generator\Printer
 */
final class Per2PhpPrinter extends Standard
{
    public function __construct(array $options = [])
    {
        $options['shortArraySyntax'] ??= true;

        parent::__construct($options);
    }

    #[\Override]
    protected function preprocessNodes(array $nodes): void
    {
        $this->canUseSemicolonNamespaces = false;
    }

    #[\Override]
    protected function pStmt_Declare(Declare_ $node): string
    {
        $definition = 'declare(' . $this->pCommaSeparated($node->declares) . ')';

        if ($node->stmts === null) {
            return $definition . ';';
        }

        return $definition . ' {' . $this->pStmts($node->stmts) . $this->nl . '}';
    }

    #[\Override]
    protected function pMaybeMultiline(array $nodes, bool $trailingComma = false): string
    {
        $isInline = $this->hasNodeWithComments($nodes) || \count($nodes) > 3;

        if (!$isInline) {
            return $this->pCommaSeparated($nodes);
        }

        return $this->pCommaSeparatedMultiline($nodes, $trailingComma) . $this->nl;
    }
}
