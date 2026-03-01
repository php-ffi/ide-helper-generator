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

    protected function preprocessNodes(array $nodes): void
    {
        $this->canUseSemicolonNamespaces = false;
    }

    protected function pStmt_Declare(Declare_ $node): string
    {
        return 'declare(' . $this->pCommaSeparated($node->declares) . ')'
            . (null !== $node->stmts ? ' {' . $this->pStmts($node->stmts) . $this->nl . '}' : ';');
    }
}
