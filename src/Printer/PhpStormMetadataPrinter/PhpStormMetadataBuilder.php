<?php

declare(strict_types=1);

namespace FFI\Generator\Printer\PhpStormMetadataPrinter;

use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr as PhpExpression;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;

final readonly class PhpStormMetadataBuilder
{
    /**
     * @param non-empty-string $argumentSetName
     * @param list<Arg> $args
     */
    public function createRegisterArgumentsSet(string $argumentSetName, array $args): FuncCall
    {
        return new FuncCall(
            name: new Name('registerArgumentsSet'),
            args: [
                new Arg(new String_($argumentSetName)),
                ...$args,
            ],
        );
    }

    /**
     * @param non-empty-string $argumentSetName
     */
    public function createArgumentsSet(string $argumentSetName): FuncCall
    {
        return new FuncCall(
            name: new Name('argumentsSet'),
            args: [new Arg(new String_($argumentSetName))],
        );
    }

    /**
     * @param non-empty-string $class
     * @param non-empty-string $method
     * @param int<0, max> $position
     * @param non-empty-string $argumentSetName
     */
    public function createExpectedArguments(
        string $class,
        string $method,
        int $position,
        string $argumentSetName,
    ): FuncCall {
        return new FuncCall(
            name: new Name('expectedArguments'),
            args: [
                new Arg(new StaticCall(
                    new FullyQualified($class),
                    $method,
                )),
                new Arg(new Int_($position)),
                new Arg($this->createArgumentsSet($argumentSetName)),
            ],
        );
    }

    /**
     * @param non-empty-string $class
     * @param non-empty-string $method
     * @param non-empty-string $argumentSetName
     */
    public function createExpectedReturnValues(
        string $class,
        string $method,
        string $argumentSetName,
    ): FuncCall {
        return new FuncCall(
            name: new Name('expectedReturnValues'),
            args: [
                new Arg(new StaticCall(
                    new FullyQualified($class),
                    $method,
                )),
                new Arg($this->createArgumentsSet($argumentSetName)),
            ],
        );
    }

    /**
     * @param int<0, max> $position
     */
    public function createType(int $position): FuncCall
    {
        return new FuncCall(
            name: new Name('type'),
            args: [
                new Arg(new Int_($position)),
            ],
        );
    }

    /**
     * @param array<string, string> $types
     */
    public function createMap(array $types): FuncCall
    {
        $items = [];

        foreach ($types as $name => $value) {
            $items[] = new ArrayItem(
                value: new String_($value),
                key: new String_($name),
            );
        }

        return new FuncCall(
            name: new Name('map'),
            args: [
                new Arg(new Array_($items)),
            ],
        );
    }

    /**
     * @param non-empty-string $class
     * @param non-empty-string $method
     * @param int<0, max> $position
     */
    public function createOverride(
        string $class,
        string $method,
        int $position,
        PhpExpression $value,
    ): FuncCall {
        return new FuncCall(
            name: new Name('override'),
            args: [
                new Arg(new StaticCall(
                    class: new FullyQualified($class),
                    name: $method,
                    args: [
                        new Arg(new Int_($position)),
                    ],
                )),
                new Arg($value),
            ],
        );
    }
}
