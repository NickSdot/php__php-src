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
use function max;
use function sapi_windows_vt100_support;
use function sprintf;
use function str_pad;
use function str_repeat;
use function strlen;
use function stream_isatty;

final class Output
{
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
        $supported = function_exists('stream_isatty')
            && stream_isatty(STDERR)
            && getenv('NO_COLOR') === false
            && getenv('TERM') !== 'dumb'
            && (PHP_OS_FAMILY !== 'Windows'
                || (function_exists('sapi_windows_vt100_support')
                && sapi_windows_vt100_support(STDERR)));

        if ($supported === true) {
            return $colour;
        }

        return '';
    }
}
