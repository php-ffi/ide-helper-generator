<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser;

use FFI\Generator\Exception\ProcessException;
use FFI\Generator\Exception\ProcessExecutionException;
use FFI\Generator\Exception\ProcessOutputException;
use Symfony\Component\Process\Process;

final class CastXmlGenerator
{
    /**
     * @var non-empty-string
     */
    private const string PCRE_TPL_VERSION = '/%s\hversion\h(\d+\.\d+\.\d+(?:-\d+)?)/iu';

    public const string DEFAULT_CAST_XML_BINARY = 'castxml';

    public const ?string DEFAULT_TEMP_DIRECTORY = null;

    public const array DEFAULT_INCLUDE_FILES = [
        'stddef.h',
        'stdint.h',
    ];

    public const array DEFAULT_INCLUDE_DIRECTORIES = [];

    /**
     * Temp directory.
     *
     * @var non-empty-string
     */
    public readonly string $tempDirectory;

    public string $castXmlVersion {
        get => $this->castXmlVersion ??= $this->parseVersionSection('castxml');
    }

    public string $clangVersion {
        get => $this->clangVersion ??= $this->parseVersionSection('clang');
    }

    public bool $isAvailable {
        get {
            if (!isset($this->isAvailable)) {
                try {
                    $this->run();
                } catch (\Throwable) {
                    return $this->isAvailable = false;
                }

                return $this->isAvailable = true;
            }

            return $this->isAvailable;
        }
    }

    /**
     * @param non-empty-string|null $tempDirectory
     */
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $castXmlBinary = self::DEFAULT_CAST_XML_BINARY,
        ?string $tempDirectory = self::DEFAULT_TEMP_DIRECTORY,
        /**
         * @var list<non-empty-string>
         */
        public readonly array $includeFiles = self::DEFAULT_INCLUDE_FILES,
        /**
         * @var list<non-empty-string>
         */
        public readonly array $includeDirectories = self::DEFAULT_INCLUDE_DIRECTORIES,
    ) {
        $this->tempDirectory = $tempDirectory ?? \sys_get_temp_dir();
    }

    private function parseVersionSection(string $prefix): string
    {
        $result = $this->run(['--version']);

        $pcre = \sprintf(self::PCRE_TPL_VERSION, \preg_quote($prefix, '/'));
        \preg_match($pcre, $result, $output);

        if (!isset($output[1])) {
            throw ProcessOutputException::becauseOutputIsInvalid($prefix, $result);
        }

        return $output[1];
    }

    /**
     * @param list<non-empty-string> $args
     * @param non-empty-string|null $cwd
     */
    public function run(array $args = [], ?string $cwd = null): string
    {
        $cwd ??= \getcwd() ?: '.';

        $process = new Process([$this->castXmlBinary, ...$args], $cwd);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessExecutionException($process->getErrorOutput(), $process->getExitCode() ?? -1);
        }

        return $process->getOutput();
    }

    /**
     * @param non-empty-string $pathname
     *
     * @return non-empty-string
     */
    private function getOutputFilename(string $pathname): string
    {
        return $this->tempDirectory . '/'
            . \pathinfo($pathname, \PATHINFO_FILENAME) . '.xml';
    }

    /**
     * @param non-empty-string $pathname
     * @param non-empty-string|null $cwd
     */
    public function generate(string $pathname, ?string $cwd = null): CastXmlGeneratorResult
    {
        if (!\is_file($pathname)) {
            throw new ProcessException(\sprintf('File "%s" not found', $pathname));
        }

        $processOptions = $this->createProcessOptions(
            inputPathname: $pathname,
            outputPathname: $outputPathname = $this->getOutputFilename($pathname),
            cwd: $cwd,
        );

        $this->run($processOptions, $cwd ?? \dirname($pathname));

        if (!\is_file($outputPathname)) {
            throw new ProcessException('Generated file not available');
        }

        return new CastXmlGeneratorResult(
            pathname: $this->normalizePath($outputPathname),
        );
    }

    /**
     * @param non-empty-string $inputPathname
     * @param non-empty-string $outputPathname
     * @param non-empty-string|null $cwd
     *
     * @return list<non-empty-string>
     */
    private function createProcessOptions(string $inputPathname, string $outputPathname, ?string $cwd = null): array
    {
        $options = [$inputPathname, '--castxml-output=1', '-o', $outputPathname];

        foreach ($this->createIncludeOptions($inputPathname, $cwd) as $option) {
            $options[] = $option;
        }

        return $options;
    }

    /**
     * @param non-empty-string $pathname
     * @param non-empty-string|null $cwd
     *
     * @return list<non-empty-string>
     */
    private function createIncludeOptions(string $pathname, ?string $cwd = null): array
    {
        $result = [];

        $result[] = $this->createIncludeDirectoryOption(\dirname($pathname));

        if ($cwd !== null && $cwd !== '.' && $cwd !== '') {
            $result[] = $this->createIncludeDirectoryOption($cwd);
        }

        foreach ($this->includeDirectories as $directoryToInclude) {
            $result[] = $this->createIncludeDirectoryOption($directoryToInclude);
        }

        foreach ($this->includeFiles as $fileToInclude) {
            $result[] = $this->createIncludeFileOption($fileToInclude);
        }

        return \array_values(\array_unique($result));
    }

    /**
     * @param non-empty-string $pathname
     *
     * @return non-empty-string
     */
    private function createIncludeFileOption(string $pathname): string
    {
        return \sprintf('--include=%s', $this->normalizePath($pathname));
    }

    /**
     * @param non-empty-string $directory
     *
     * @return non-empty-string
     */
    private function createIncludeDirectoryOption(string $directory): string
    {
        return \sprintf('--include-directory=%s', $this->normalizePath($directory));
    }

    private function normalizePath(string $path): string
    {
        if (($realPath = \realpath($path)) !== false) {
            $path = $realPath;
        }

        return \str_replace(['\\', '/'], \DIRECTORY_SEPARATOR, $path);
    }
}
