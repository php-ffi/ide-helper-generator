<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

/**
 * @template T of TypeInterface
 *
 * @template-implements GenericTypeInterface<T>
 */
final class PointerTypeNode extends TypeNode implements GenericTypeInterface
{
    public function __construct(
        /**
         * @var T
         */
        public readonly TypeInterface $type,
    ) {}

    /**
     * @template TArgType of TypeInterface
     * @param class-string<TArgType> $class
     * @return TArgType|null
     */
    public function to(string $class): ?TypeInterface
    {
        $type = $this;

        do {
            if ($type instanceof $class) {
                return $type;
            }

            $type = $type->type;
        } while ($type instanceof TypeModifierInterface);

        if ($type instanceof $class) {
            return $type;
        }

        return null;
    }
}
