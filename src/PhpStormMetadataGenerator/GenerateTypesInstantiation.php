<?php

declare(strict_types=1);

namespace FFI\Generator\PhpStormMetadataGenerator;

use FFI\Generator\Generator\TypeInfoGenerator;
use FFI\Generator\NamingStrategyInterface;
use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\Type\RecordTypeNode;
use FFI\Generator\Node\Type\TypeDefinitionNode;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;

/**
 * Generates:
 *  - registerArgumentsSet('<FFI_GLOBAL_TYPE_NAMES>', ...);
 *  - expectedArguments(\FFI::new(), 0, argumentsSet('<FFI_GLOBAL_TYPE_NAMES>'));
 *  - expectedArguments(\FFI::cast(), 0, argumentsSet('<FFI_GLOBAL_TYPE_NAMES>'));
 *  - expectedArguments(\FFI::type(), 0, argumentsSet('<FFI_GLOBAL_TYPE_NAMES>'));
 */
final class GenerateTypesInstantiation extends Visitor
{
    private const COMMENT = 'List of available FFI type names';

    private readonly TypeInfoGenerator $info;

    /**
     * @param list<non-empty-string> $excludes
     * @param non-empty-string $globalArgumentSetSuffix
     */
    public function __construct(
        private readonly NamingStrategyInterface $naming,
        private readonly string $argumentSetPrefix,
        private readonly string $globalArgumentSetSuffix,
        private readonly array $excludes = [],
        private readonly int $pointersInheritance = 2,
    ) {
        $this->info = new TypeInfoGenerator($this->naming);
    }

    public function after(NamespaceNode $ctx, iterable $nodes): iterable
    {
        $registerArgumentsSet = new FuncCall(
            name: new Name('registerArgumentsSet'),
            args: [$this->argNode($this->getArgumentsSetName(), self::COMMENT)]
        );

        foreach (TypeInfoGenerator::getBuiltinTypeNames() as $name) {
            $registerArgumentsSet->args[$name] = $this->argNode($name);
        }

        foreach ($nodes as $node) {
            // Skip non-user creatable types
            if (!$this->isUserCreatable($node, $this->excludes)) {
                continue;
            }

            assert($node->name !== null, new \TypeError('Type name required'));

            // Ignore already registered types
            if (isset($registerArgumentsSet->args[$node->name])) {
                continue;
            }

            $registerArgumentsSet->args[$node->name] = $this->argNode($node->name);

            /**
             * Typedef terminal reference
             *
             * @var TypeDefinitionNode $node
             */
            $type = $this->getDefinitionOf($node);

            if ($type instanceof RecordTypeNode) {
                for ($i = 1; $i <= $this->pointersInheritance; ++$i) {
                    $suffix = \str_repeat('*', $i);

                    $registerArgumentsSet->args[] = new Arg(new String_($node->name . $suffix));
                }
            }
        }

        //
        // Return result
        //

        // registerArgumentsSet('<FFI_GLOBAL_TYPE_NAMES>', ...);
        yield new Expression($registerArgumentsSet);

        // expectedArguments(\FFI::new(), 0, argumentsSet('<FFI_GLOBAL_TYPE_NAMES'));
        yield new Expression($this->expectedArgumentsForFunction('new'));

        // expectedArguments(\FFI::cast(), 0, argumentsSet('<FFI_GLOBAL_TYPE_NAMES'));
        yield new Expression($this->expectedArgumentsForFunction('cast'));

        // expectedArguments(\FFI::type(), 0, argumentsSet('<FFI_GLOBAL_TYPE_NAMES'));
        yield new Expression($this->expectedArgumentsForFunction('type'));
    }

    /**
     * @return non-empty-string
     */
    private function getArgumentsSetName(): string
    {
        return $this->argumentSetPrefix . $this->globalArgumentSetSuffix;
    }

    /**
     * @param non-empty-string $name
     */
    private function expectedArgumentsForFunction(string $name): FuncCall
    {
        return $this->expectedArguments(
            class: $this->naming->getEntrypoint(),
            method: $name,
            willExpects: $this->argumentsSet($this->getArgumentsSetName()),
        );
    }
}
