<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function array_diff;
use function array_fill_keys;
use function array_filter;
use function array_intersect;
use function array_keys;
use function array_values;
use function count;
use function hash_file;
use function strcmp;
use function usort;

final readonly class PhptChanges
{
    /** @var list<PhptChange> */
    private array $changes;

    /** @param list<PhptChange> $changes */
    public function __construct(array $changes = [])
    {
        usort($changes, fn(PhptChange $first, PhptChange $second): int => strcmp($first->path(), $second->path()));
        $this->changes = $changes;
    }

    /** @param array<string, string> $renamedPaths */
    public static function between(PhptResults $base, PhptResults $tree, array $renamedPaths, string $baseSource, string $treeSource): self
    {
        $changes = [];

        $basePaths = $base->paths();
        $treePaths = $tree->paths();

        $created = array_fill_keys(array_diff($treePaths, $basePaths), true);
        $deleted = array_fill_keys(array_diff($basePaths, $treePaths), true);

        self::addRenames($changes, $created, $deleted, $renamedPaths, $base, $tree);

        $exactRenames = self::exactRenames($deleted, $created, $baseSource, $treeSource);

        self::addRenames($changes, $created, $deleted, $exactRenames, $base, $tree);

        foreach (array_keys($created) as $path) {
            $changes[] = new PhptChange(null, $path, null, $tree->status($path));
        }

        foreach (array_keys($deleted) as $path) {
            $changes[] = new PhptChange($path, null, $base->status($path), null);
        }

        foreach (array_intersect($basePaths, $treePaths) as $path) {

            $change = new PhptChange($path, $path, $base->status($path), $tree->status($path));

            if ($change->skipped() === true) {
                $changes[] = $change;
            }
        }

        return new self($changes);
    }

    /** @return list<PhptChange> */
    public function created(): array
    {
        return $this->matching(fn(PhptChange $change): bool => $change->created());
    }

    /** @return list<PhptChange> */
    public function deleted(): array
    {
        return $this->matching(fn(PhptChange $change): bool => $change->deleted());
    }

    /** @return list<PhptChange> */
    public function renamed(): array
    {
        return $this->matching(fn(PhptChange $change): bool => $change->renamed());
    }

    /** @return list<PhptChange> */
    public function skipped(): array
    {
        $changes = $this->matching(fn(PhptChange $change): bool => $change->skipped());

        usort($changes, function (PhptChange $first, PhptChange $second): int {

            $rank = $this->skipRank($first) <=> $this->skipRank($second);

            return $rank !== 0 ? $rank : strcmp($first->path(), $second->path());
        });

        return $changes;
    }

    /**
     * @param list<PhptChange> $changes
     * @param array<string, true> $created
     * @param array<string, true> $deleted
     * @param array<string, string> $renamedPaths
     */
    private static function addRenames(array &$changes, array &$created, array &$deleted, array $renamedPaths, PhptResults $base, PhptResults $tree): void
    {
        foreach ($renamedPaths as $basePath => $treePath) {

            if (isset($deleted[$basePath], $created[$treePath]) === false) {
                continue;
            }

            $changes[] = new PhptChange($basePath, $treePath, $base->status($basePath), $tree->status($treePath));

            unset($deleted[$basePath], $created[$treePath]);
        }
    }

    /**
     * @param array<string, true> $deleted
     * @param array<string, true> $created
     * @return array<string, string>
     */
    private static function exactRenames(array $deleted, array $created, string $baseSource, string $treeSource): array
    {
        $renamedPaths = [];

        $baseHashes = self::hashes(array_keys($deleted), $baseSource);
        $treeHashes = self::hashes(array_keys($created), $treeSource);

        foreach ($baseHashes as $hash => $basePaths) {

            $treePaths = $treeHashes[$hash] ?? [];

            if (count($basePaths) === 1 && count($treePaths) === 1) {
                $renamedPaths[$basePaths[0]] = $treePaths[0];
            }
        }

        return $renamedPaths;
    }

    /**
     * @param list<string> $paths
     * @return array<string, list<string>>
     */
    private static function hashes(array $paths, string $source): array
    {
        $hashes = [];

        foreach ($paths as $path) {

            $hash = hash_file('sha256', "$source/$path");

            if ($hash === false) {
                throw new RuntimeException("Could not read test: $path");
            }

            $hashes[$hash][] = $path;
        }

        return $hashes;
    }

    /**
     * @param callable(PhptChange): bool $matches
     * @return list<PhptChange>
     */
    private function matching(callable $matches): array
    {
        return array_values(array_filter($this->changes, $matches));
    }

    private function skipRank(PhptChange $change): int
    {
        if ($change->baseStatus !== PhptResults::SKIP && $change->treeStatus === PhptResults::SKIP) {
            return 0;
        }

        if ($change->baseStatus === PhptResults::SKIP && $change->treeStatus === PhptResults::SKIP) {
            return 1;
        }

        return 2;
    }
}
