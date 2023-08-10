<?php

declare(strict_types=1);

namespace FFI\Generator\Node;

interface OptionalNamedNodeInterface extends NodeInterface
{
    /**
     * @return non-empty-string|null
     */
    public function getName(): ?string;
}
