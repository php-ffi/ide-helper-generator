<?php

declare(strict_types=1);

namespace FFI\AutocompleteGenerator\PhpStormMetadataGenerator;

use FFI\AutocompleteGenerator\Generator\TypeInfo;
use FFI\AutocompleteGenerator\Generator\TypeInfoGenerator;
use FFI\AutocompleteGenerator\NamingStrategyInterface;
use FFI\AutocompleteGenerator\Node\FunctionNode;
use FFI\AutocompleteGenerator\Node\NamespaceNode;
use FFI\AutocompleteGenerator\Node\Type\TypeDefinitionNode;
use PhpParser\Builder\Interface_;
use PhpParser\Builder\Param;
use PhpParser\Comment\Doc;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_ as ClassStatement;
use PhpParser\Node\Stmt\ClassMethod;

/**
 * Generates FFI functions
 */
final class GenerateExportFunctions extends Visitor
{
    private readonly TypeInfoGenerator $info;

    /**
     * @var list<ClassMethod>
     */
    private array $methods = [];

    /**
     * @param list<non-empty-string> $excludes
     */
    public function __construct(
        private readonly NamingStrategyInterface $naming,
        private readonly array $excludes = [],
    ) {
        $this->info = new TypeInfoGenerator($naming);
    }

    public function enter(NamespaceNode $ctx, FunctionNode|TypeDefinitionNode $node): iterable
    {
        if (!$node instanceof FunctionNode || $node->name === null || !$node->location->matches($this->excludes)) {
            return [];
        }

        $tags = [];

        $function = new ClassMethod($node->name);
        $function->flags = ClassStatement::MODIFIER_PUBLIC;

        //
        // Generate function's arguments
        //

        foreach ($node->arguments as $i => $argument) {
            $phpArgumentName = $argument->name ?? '_' . $i;
            $phpArgumentType = $this->info->get($ctx, $argument->type);

            $param = new Param($phpArgumentName);
            $param->setType($phpArgumentType->getPhpTypeAsString());

            if (($phpArgTypeDocString = $phpArgumentType->getDocTypeAsString()) && $phpArgTypeDocString !== 'mixed') {
                $phpArgTypeDocString = \str_replace('\Closure', 'callable', $phpArgTypeDocString);

                $tags[] = '@param ' . $phpArgTypeDocString . ' $' . $phpArgumentName;
            }

            if ($argument->variadic) {
                $param->makeVariadic();
            }

            if ($phpArgumentType->expectedValues !== []) {
                $param->addAttribute($this->getExpectedValuesAttrGroup($phpArgumentType));
            }

            $function->params[] = $param->getNode();
        }

        //
        // Apply function's return type
        //

        $phpReturnType = $this->info->get($ctx, $node->returns);

        if (($phpArgTypeDocString = $phpReturnType->getDocTypeAsString()) && $phpArgTypeDocString !== 'mixed') {
            $tags[] = '@return ' . $phpArgTypeDocString;
        }

        if ($phpReturnType->expectedValues !== []) {
            $function->attrGroups[] = $this->getExpectedValuesAttrGroup($phpReturnType);
        }

        $function->returnType = new Identifier($phpReturnType->getPhpTypeAsString());

        //
        // Generate php docs
        //

        if ($tags !== []) {
            $tagsString = \implode("\n * ", $tags);

            $function->setDocComment(new Doc(<<<PHPDOC
                /**
                 * $tagsString
                 */
                PHPDOC));
        }


        $this->methods[] = $function;

        return [];
    }

    private function getExpectedValuesAttrGroup(TypeInfo $info): AttributeGroup
    {
        $arguments = [];

        foreach ($info->expectedValues as $value) {
            [$expectedValueClass, $expectedValueCase] = \explode('::', $value);

            $arguments[] = new ArrayItem(
                value: new ClassConstFetch(
                    class: new FullyQualified(\trim($expectedValueClass, '\\')),
                    name: new Identifier($expectedValueCase),
                ),
            );
        }

        return new AttributeGroup([new Attribute(
            name: new FullyQualified('JetBrains\PhpStorm\ExpectedValues'),
            args: [
                new Arg(
                    value: new Array_($arguments),
                    name: new Identifier('flags'),
                )
            ],
        )]);
    }

    public function after(NamespaceNode $ctx, iterable $nodes): iterable
    {
        $context = new Interface_($this->naming->getEntrypointClassName());

        $context->addStmts($this->methods);

        yield $context->getNode();

        $this->methods = [];
    }
}
