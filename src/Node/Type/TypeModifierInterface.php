<?php

declare(strict_types=1);

namespace FFI\Generator\Node\Type;

/**
 * @template T of TypeInterface
 *
 * @template-extends GenericTypeInterface<T>
 */
interface TypeModifierInterface extends GenericTypeInterface {}
