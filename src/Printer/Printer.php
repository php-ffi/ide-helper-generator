<?php

declare(strict_types=1);

namespace FFI\Generator\Printer;

use FFI\Generator\Printer\Internal\Per2PhpPrinter;
use FFI\Generator\TypeResolver\Factory\TypeFactory;
use FFI\Generator\TypeResolver\Factory\TypeFactoryInterface;
use PhpParser\PrettyPrinter as PhpPrinterInterface;
use TypeLang\Printer\NativeTypePrinter;
use TypeLang\Printer\PrettyPrinter;
use TypeLang\Printer\PrinterInterface as TypePrinterInterface;

abstract readonly class Printer implements PrinterInterface
{
    protected TypeFactoryInterface $typeFactory;

    protected PhpPrinterInterface $phpPrinter;
    protected TypePrinterInterface $phpTypePrinter;
    protected TypePrinterInterface $phpdocTypePrinter;

    public function __construct(
        ?TypeFactoryInterface $typeFactory = null,
        ?PhpPrinterInterface $phpPrinter = null,
        ?TypePrinterInterface $phpTypePrinter = null,
        ?TypePrinterInterface $phpdocTypePrinter = null,
    ) {
        $this->phpPrinter = $phpPrinter ?? new Per2PhpPrinter();
        $this->typeFactory = $typeFactory ?? TypeFactory::default();
        $this->phpTypePrinter = $phpTypePrinter ?? new NativeTypePrinter();
        $this->phpdocTypePrinter = $phpdocTypePrinter ?? new PrettyPrinter(
            wrapUnionType: false,
        );
    }
}
