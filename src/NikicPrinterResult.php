<?php

declare(strict_types=1);

namespace FFI\Generator;

use PhpParser\Comment;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;

final class NikicPrinterResult implements ResultInterface
{
    private readonly PrettyPrinterAbstract $printer;

    /**
     * @param list<Stmt> $statements
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    public function __construct(
        private readonly NamingStrategyInterface $naming,
        private readonly array $statements,
    ) {
        $this->printer = new class (['shortArraySyntax' => true]) extends Standard {
            protected function preprocessNodes(array $nodes): void
            {
                $this->canUseSemicolonNamespaces = false;
            }

            protected function pSingleQuotedString(string $string): string
            {
                return '\'' . \addcslashes($string, '\'') . '\'';
            }

            protected function pExpr_Closure(Closure $node): string
            {
                return $this->pAttrGroups($node->attrGroups, true)
                    . ($node->static ? 'static ' : '')
                    . 'function ' . ($node->byRef ? '&' : '')
                    . '(' . $this->pCommaSeparated($node->params) . ')'
                    . (!empty($node->uses) ? ' use(' . $this->pCommaSeparated($node->uses) . ')' : '')
                    . (null !== $node->returnType ? ': ' . $this->p($node->returnType) : '')
                    . ' {' . $this->pStmts($node->stmts) . $this->nl . '}';
            }

            /**
             * @psalm-suppress MixedOperand
             */
            protected function pStmt_ClassMethod(Stmt\ClassMethod $node): string
            {
                return $this->pAttrGroups($node->attrGroups)
                    . $this->pModifiers($node->flags)
                    . 'function ' . ($node->byRef ? '&' : '') . $node->name
                    . '(' . $this->pMaybeMultiline($node->params) . ')'
                    . (null !== $node->returnType ? ': ' . $this->p($node->returnType) : '')
                    . (null !== $node->stmts
                        ? $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}'
                        : ';');
            }

            protected function pStmt_Function(Stmt\Function_ $node): string
            {
                return $this->pAttrGroups($node->attrGroups)
                    . 'function ' . ($node->byRef ? '&' : '') . $node->name
                    . '(' . $this->pCommaSeparated($node->params) . ')'
                    . (null !== $node->returnType ? ': ' . $this->p($node->returnType) : '')
                    . $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}';
            }
        };
    }

    public function __toString(): string
    {
        $statements = $this->statements;

        foreach ($statements as $i => $statement) {
            if ($statement instanceof Stmt\Namespace_ && $statement->stmts === []) {
                unset($statements[$i]);
            }
        }

        $entrypoint = $this->naming->getEntrypoint();

        return $this->printer->prettyPrintFile([
            new Stmt\Declare_(
                declares: [new Stmt\DeclareDeclare('strict_types', new LNumber(1))],
                attributes: [
                    'comments' => [
                        new Comment('// @formatter:off'),
                        new Comment('// phpcs:ignoreFile'),
                        new Comment(<<<PHPDOC

                            /**
                             * A helper file for FFI, to provide autocomplete information to your IDE
                             * Generated for FFI {@see $entrypoint}.
                             *
                             * This file should not be included in your code, only analyzed by your IDE!
                             *
                             * @author Nesmeyanov Kirill <nesk@xakep.ru>
                             * @see https://github.com/php-ffi/ide-helper-generator
                             *
                             * @psalm-suppress all
                             */

                            PHPDOC),
                    ]
                ]
            ),
            ...$statements,
        ]);
    }
}
