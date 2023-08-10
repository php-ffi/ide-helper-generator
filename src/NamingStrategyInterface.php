<?php

declare(strict_types=1);

namespace FFI\Generator;

use FFI\Generator\Node\OptionalNamedNodeInterface;

interface NamingStrategyInterface
{
    /**
     * @return non-empty-string
     */
    public function getEntrypoint(): string;

    /**
     * @return string
     */
    public function getEntrypointNamespace(): string;

    /**
     * @return non-empty-string
     */
    public function getEntrypointClassName(): string;

    /**
     * @param non-empty-string $name
     * @return non-empty-string
     */
    public function getName(string $name, OptionalNamedNodeInterface $type): string;
}
