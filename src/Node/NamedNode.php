<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

/**
 * @property-read non-empty-string $name
 */
abstract class NamedNode extends OptionalNamedNode implements NamedNodeInterface
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
