<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function array_keys;
use function count;
use function explode;
use function file;
use function ksort;
use function rtrim;
use function str_replace;
use function str_starts_with;
use function strlen;
use function strrpos;
use function substr;

final readonly class PhptResults
{
    public const string SKIP = 'SKIP';

    /** @var array<string, string> */
    private array $statuses;

    private int $count;

    /** @param array<string, string> $statuses */
    public function __construct(array $statuses = [], ?int $count = null)
    {
        ksort($statuses);

        $this->statuses = $statuses;
        $this->count = $count ?? count($statuses);
    }

    public static function fromFile(string $file, string $source): self
    {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw new RuntimeException('Could not read test results');
        }

        $statuses = [];
        $source = rtrim(str_replace('\\', '/', $source), '/');

        foreach ($lines as $line) {

            $result = explode("\t", $line, 2);

            if (count($result) !== 2) {
                throw new RuntimeException('Could not parse test results');
            }

            [$status, $path] = $result;
            $statuses[self::path($path, $source)] = self::statusName($status);
        }

        return new self($statuses, count($lines));
    }

    public function count(): int
    {
        return $this->count;
    }

    /** @return list<string> */
    public function paths(): array
    {
        return array_keys($this->statuses);
    }

    public function status(string $path): ?string
    {
        return $this->statuses[$path] ?? null;
    }

    private static function statusName(string $status): string
    {
        return match ($status) {
            'BORKED' => 'BORK',
            'FAILED' => 'FAIL',
            'LEAKED' => 'LEAK',
            'PASSED' => 'PASS',
            'SKIPPED' => self::SKIP,
            'WARNED' => 'WARN',
            'XFAILED' => 'XFAIL',
            'XLEAKED' => 'XLEAK',
            default => $status,
        };
    }

    private static function path(string $path, string $source): string
    {
        if (str_starts_with($path, '# ') === true) {

            $separator = strrpos($path, ': ');

            if ($separator === false) {
                throw new RuntimeException('Could not parse redirected test result');
            }

            $path = substr($path, $separator + 2);
        }

        $path = str_replace('\\', '/', $path);

        if (str_starts_with($path, "$source/") === true) {
            return substr($path, strlen($source) + 1);
        }

        return Path::repository($path);
    }
}
