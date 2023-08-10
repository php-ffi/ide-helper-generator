<?php

declare(strict_types=1);

namespace FFI\Generator\Metadata;

use FFI\Generator\Exception\ProcessException;
use FFI\Generator\Metadata\Result\Result;
use FFI\Generator\Metadata\Result\SourceResult;
use Symfony\Component\Process\Process;

final class CastXMLGenerator implements GeneratorInterface
{
    /**
     * @var non-empty-string
     */
    private const PCRE_TPL_VERSION = '/%s\hversion\h(\d+\.\d+\.\d+(?:-\d+)?)/iu';

    /**
     * @var non-empty-string
     */
    private const DEFAULT_BINARY = 'castxml';

    /**
     * Temp directory.
     *
     * @var non-empty-string
     */
    private string $temp;

    /**
     * @psalm-taint-sink file $binary
     * @psalm-taint-sink file $temp
     *
     * @param non-empty-string $binary
     * @param non-empty-string|null $temp
     */
    public function __construct(
        private readonly string $binary = self::DEFAULT_BINARY,
        ?string $temp = null,
    ) {
        $this->temp = $temp ?? \sys_get_temp_dir() ?: '.temp';
    }

    /**
     * @psalm-taint-sink file $directory
     * @param non-empty-string $directory
     */
    public function withTempDirectory(string $directory): self
    {
        $self = clone $this;
        $self->temp = $directory;

        return $self;
    }

    public function getVersion(): string
    {
        return $this->parseVersionSection('castxml');
    }

    private function parseVersionSection(string $prefix): string
    {
        $result = $this->run('--version');

        $pcre = \sprintf(self::PCRE_TPL_VERSION, \preg_quote($prefix, '/'));
        \preg_match($pcre, $result, $output);

        if (!isset($output[1])) {
            throw new ProcessException(<<<MESSAGE
                Can not parse version section.

                Actual CastXML output:
                $result
                MESSAGE);
        }

        return $output[1];
    }

    private function run(string ...$args): string
    {
        return $this->runIn(\getcwd() ?: '.', ...$args);
    }

    private function runIn(string $cwd, string ...$args): string
    {
        $process = new Process([$this->binary, ...$args], $cwd);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessException($process->getErrorOutput(), (int)$process->getExitCode());
        }

        return $process->getOutput();
    }

    public function getClangVersion(): string
    {
        return $this->parseVersionSection('clang');
    }

    public function isAvailable(): bool
    {
        try {
            $this->run();
        } catch (\Throwable) {
            return false;
        }

        return true;
    }

    /**
     * @param list<non-empty-string> $includes
     */
    public function generate(string $filename, string $cwd = null, array $includes = []): Result
    {
        if (!\is_file($filename)) {
            throw new ProcessException(\sprintf('File "%s" not found', $filename));
        }

        $out = $this->getTempDirectory() . '/' . \basename($filename, '.h') . '.xml';

        $options = ['--castxml-output=1', '-o', $out];

        foreach ($this->getIncludeOptions($filename, $cwd, $includes) as $option) {
            $options[] = $option;
        }

        $this->runIn($cwd ?? \dirname($filename), $filename, ...$options);

        if (!\is_file($out)) {
            throw new ProcessException('Generated file not available');
        }

        return SourceResult::createFromFilename($out, true);
    }

    /**
     * @param list<non-empty-string> $includes
     * @return list<non-empty-string>
     */
    private function getIncludeOptions(string $filename, string $cwd = null, array $includes = []): array
    {
        $includes[] = \dirname($filename);

        if ($cwd !== null && $cwd !== '.' && $cwd !== '') {
            $includes[] = $cwd;
        }

        $result = [];

        foreach ($includes as $include) {
            $result[] = '--include-directory=' . $include;
        }

        return $result;
    }

    /**
     * @return non-empty-string
     */
    public function getTempDirectory(): string
    {
        return $this->temp;
    }
}
