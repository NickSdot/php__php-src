<?php

declare(strict_types=1);

namespace PHP\Testing;

use function array_fill;
use function array_map;
use function count;
use function function_exists;
use function fwrite;
use function getenv;
use function implode;
use function in_array;
use function max;
use function sapi_windows_vt100_support;
use function sprintf;
use function str_pad;
use function str_repeat;
use function strlen;
use function stream_isatty;

final class Output
{
    /** @var list<string> */
    private array $progressHeader = [];

    /** @var list<string> */
    private array $progressLines = [];

    private bool $interactive;

    private int $renderedProgressLines = 0;

    public function __construct(?bool $interactive = null)
    {
        $this->interactive = $interactive ?? $this->supportsTerminal(STDOUT);
    }

    public function line(string $message = '', mixed ...$values): string
    {
        if ($values !== []) {
            $message = sprintf($message, ...$values);
        }

        return $message . "\n";
    }

    /** @param list<string> $lines */
    public function lines(array $lines): string
    {
        return $this->line(implode("\n", $lines));
    }

    public function printLine(string $message = '', mixed ...$values): void
    {
        echo $this->line($message, ...$values);
    }

    public function error(string $message): void
    {
        fwrite(STDERR, $this->line($this->colour("\033[31m") . "Error: $message" . $this->colour("\033[0m")));
    }

    public function warning(string $message): void
    {
        fwrite(STDERR, $this->line($this->colour("\033[33m") . "Warning: $message" . $this->colour("\033[0m")));
    }

    /** @param list<string> $header */
    public function startProgress(array $header): void
    {
        $this->progressHeader = $header;

        if ($this->interactive === true) {
            $this->renderProgress();
            return;
        }

        foreach ($header as $line) {
            $this->printLine($line);
        }
    }

    /** @param list<string> $header */
    public function updateProgressHeader(array $header): void
    {
        $previous = $this->progressHeader;
        $this->progressHeader = $header;

        if ($this->interactive === true) {
            $this->renderProgress();
            return;
        }

        foreach ($header as $line) {
            if (in_array($line, $previous, true) === false) {
                $this->printLine($line);
            }
        }
    }

    public function progress(string $message, mixed ...$values): void
    {
        if ($values !== []) {
            $message = sprintf($message, ...$values);
        }

        $this->progressLines[] = $message;

        if ($this->interactive === true) {
            $this->renderProgress();
            return;
        }

        $this->printLine($message);
    }

    public function finishProgress(): void
    {
        if ($this->interactive === true) {
            $this->progressLines = [];
            $this->renderProgress();
            $this->renderedProgressLines = 0;
        }

        $this->progressHeader = [];
        $this->progressLines = [];
    }

    /** @param list<list<string>> $rows */
    public function table(array $rows): void
    {
        $widths = array_fill(0, count($rows[0]), 0);

        foreach ($rows as $row) {
            foreach ($row as $column => $value) {
                $widths[$column] = max($widths[$column], strlen($value));
            }
        }

        $border = '+-' . implode('-+-', array_map(
            static fn(int $width): string => str_repeat('-', $width), $widths)
        ) . '-+';

        $this->printLine($border);

        foreach ($rows as $index => $row) {

            foreach ($row as $column => $value) {

                $padding = STR_PAD_LEFT;

                if ($column === 0) {
                    $padding = STR_PAD_RIGHT;
                }

                $row[$column] = str_pad($value, $widths[$column], ' ', $padding);

            }

            $this->printLine('| ' . implode(' | ', $row) . ' |');

            if ($index === 0) {
                $this->printLine($border);
            }
        }

        $this->printLine($border);
    }

    private function colour(string $colour): string
    {
        if ($this->supportsTerminal(STDERR) === true && getenv('NO_COLOR') === false) {
            return $colour;
        }

        return '';
    }

    /** @param resource $stream */
    private function supportsTerminal(mixed $stream): bool
    {
        return function_exists('stream_isatty')
            && stream_isatty($stream)
            && getenv('TERM') !== 'dumb'
            && (PHP_OS_FAMILY !== 'Windows'
                || (function_exists('sapi_windows_vt100_support')
                && sapi_windows_vt100_support($stream)));
    }

    private function renderProgress(): void
    {
        $this->clearProgress();

        $lines = [...$this->progressHeader, ...$this->progressLines];

        foreach ($lines as $line) {
            $this->printLine($line);
        }

        $this->renderedProgressLines = count($lines);
    }

    private function clearProgress(): void
    {
        if ($this->renderedProgressLines === 0) {
            return;
        }

        echo "\033[{$this->renderedProgressLines}F\033[J";
    }
}
