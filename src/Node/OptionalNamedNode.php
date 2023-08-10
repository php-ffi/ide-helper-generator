<?php

declare(strict_types=1);

namespace FFI\AutocompleteGenerator\Node;

abstract class OptionalNamedNode extends Node implements OptionalNamedNodeInterface
{
    /**
     * @param non-empty-string|null $name
     */
    public function __construct(
        public readonly ?string $name = null,
    ) {}

    public function getName(): ?string
    {
        return $this->name;
    }
}
