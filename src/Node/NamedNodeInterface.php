<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

interface NamedNodeInterface extends OptionalNamedNodeInterface
{
    /**
     * @return non-empty-string
     */
    public function getName(): string;
}
