<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

final class UnimplementedTypeNode extends TypeNode
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $name,
    ) {
        parent::__construct();
    }
}
