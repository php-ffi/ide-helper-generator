<?php

declare(strict_types=1);

namespace FFI\Generator\Support;

final readonly class BuiltinTypes
{
    /**
     * List of builtin PHP types.
     *
     * @link https://github.com/php/php-src/blob/php-8.5.0/ext/ffi/ffi.g#L176
     * @link https://github.com/php/php-src/blob/php-8.5.0/ext/ffi/ffi.g#L807
     */
    private const array BUILTIN_PHP_TYPES = [
                              // Windows x64   | Windows x86   | Linux x64
        'void *',             // void*         | void*         | void*
        'bool',               // bool          | bool          | bool
        'float',              // float         | float         | float
        'double',             // double        | double        | double
        'long double',        // double        | double        | long double
        'char',               // char          | char          | char
        'signed char',        // int8_t        | int8_t        | int8_t
        'unsigned char',      // uint8_t       | uint8_t       | uint8_t
        'int',                // int32_t       | int32_t       | int32_t
        'signed int',         // int32_t       | int32_t       | int32_t
        'unsigned int',       // uint32_t      | uint32_t      | uint32_t
        'long',               // int32_t       | int32_t       | int64_t
        'signed long',        // int32_t       | int32_t       | int64_t
        'unsigned long',      // uint32_t      | uint32_t      | uint64_t
        'long long',          // int64_t       | int64_t       | int64_t
        'signed long long',   // int64_t       | int64_t       | int64_t
        'unsigned long long', // uint64_t      | uint64_t      | uint64_t
        'intptr_t',           // int64_t       | int32_t       | int64_t
        'uintptr_t',          // uint64_t      | uint32_t      | uint64_t
        'size_t',             // uint64_t      | uint32_t      | uint64_t
        'ssize_t',            // int64_t       | int32_t       | int64_t
        'ptrdiff_t',          // int64_t       | int32_t       | int64_t
        'off_t',              // int32_t       | int32_t       | int64_t
        'va_list',            // void*         | void*         | void*
        '__builtin_va_list',  // void*         | void*         | void*
        '__gnuc_va_list',     // void*         | void*         | void*
        '_Bool',              // uint8_t       | uint8_t       | uint8_t
        'int8_t',             // int8_t        | int8_t        | int8_t
        'uint8_t',            // uint8_t       | uint8_t       | uint8_t
        'int16_t',            // int16_t       | int16_t       | int16_t
        'uint16_t',           // uint16_t      | uint16_t      | uint16_t
        'int32_t',            // int32_t       | int32_t       | int32_t
        'uint32_t',           // uint32_t      | uint32_t      | uint32_t
        'int64_t',            // int64_t       | int64_t       | int64_t
        'uint64_t',           // uint64_t      | uint64_t      | uint64_t
    ];

    /**
     * @return non-empty-list<non-empty-string>
     */
    public static function getAll(int $phpVersion = \PHP_VERSION_ID): array
    {
        if ($phpVersion < 74000) {
            return [];
        }

        return self::BUILTIN_PHP_TYPES;
    }
}
