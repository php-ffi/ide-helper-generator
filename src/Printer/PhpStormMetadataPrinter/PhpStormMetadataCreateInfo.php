<?php

declare(strict_types=1);

namespace FFI\Generator\Printer\PhpStormMetadataPrinter;

use Random\Randomizer;

final readonly class PhpStormMetadataCreateInfo
{
    public const string DEFAULT_NAMESPACE = 'PHPSTORM_META';

    public const string DEFAULT_COMMENT = <<<'COMMENT'
        /**
         * A helper file for FFI, to provide autocomplete information to your IDE
         *
         * This file should not be included in your code, only analyzed by your IDE!
         *
         * @link https://github.com/php-ffi/ide-helper-generator
         *
         * @psalm-suppress all
         */
        // @formatter:off
        // phpcs:ignoreFile
        COMMENT;

    public string $prefix;

    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $namespace = self::DEFAULT_NAMESPACE,
        public ?string $comment = self::DEFAULT_COMMENT,
        ?string $prefix = null,
    ) {
        $this->prefix = $prefix ?? $this->createPrefix();
    }

    /**
     * @return non-empty-string
     */
    private function createPrefix(): string
    {
        $bytes = new Randomizer()
            ->getBytes(8);

        return \sprintf('%s_', \bin2hex($bytes));
    }
}
