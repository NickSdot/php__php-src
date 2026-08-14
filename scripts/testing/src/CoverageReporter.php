<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function count;
use function file_put_contents;
use function implode;
use function round;
use function sort;
use function sprintf;
use function str_repeat;
use function strlen;

final class CoverageReporter
{
    public function __construct(
        private string $file,
        private Output $output = new Output()
    ) {
        $this->write($this->output->line('Coverage comparison did not complete.'));
    }

    public function report(CoverageComparisonResult $comparison, PhptRun $baseRun, PhptRun $treeRun): void
    {
        $totals = $comparison->totals();

        $missedLines = $comparison->missedLines();
        $missedBranches = $comparison->missedBranches();

        $gainedLines = $comparison->gainedLines();
        $gainedBranches = $comparison->gainedBranches();

        $this->writeCoverageReport($missedLines, $missedBranches, $gainedLines, $gainedBranches);

        $rows = [
            ['', 'Tests', 'Sources', 'Lines', 'Branches', 'Time', 'Memory'],
            [
                'Base',
                $this->countValue($baseRun->testCount),
                (string) $totals->baseSources(),
                $this->coverageValue($totals->baseLines(), $totals->baseExecutableLines()),
                $this->coverageValue($totals->baseBranches(), $totals->baseExecutableBranches()),
                sprintf('%.2fs', $baseRun->time),
                $this->formatMemory($baseRun->memory),
            ],
            [
                'Tree',
                $this->countValue($treeRun->testCount),
                (string) $totals->treeSources(),
                $this->coverageValue($totals->treeLines(), $totals->treeExecutableLines()),
                $this->coverageValue($totals->treeBranches(), $totals->treeExecutableBranches()),
                sprintf('%.2fs', $treeRun->time),
                $this->formatMemory($treeRun->memory),
            ],
            [
                'Change',
                $this->countChange($baseRun->testCount, $treeRun->testCount),
                $this->countChange($totals->baseSources(), $totals->treeSources()),
                $this->coverageChange(
                    count($gainedLines),
                    count($missedLines),
                    $totals->baseLines(),
                    $totals->baseExecutableLines(),
                    $totals->treeLines(),
                    $totals->treeExecutableLines()
                ),
                $this->coverageChange(
                    count($gainedBranches),
                    count($missedBranches),
                    $totals->baseBranches(),
                    $totals->baseExecutableBranches(),
                    $totals->treeBranches(),
                    $totals->treeExecutableBranches()
                ),
                sprintf('%+.2fs', $treeRun->time - $baseRun->time),
                $this->memoryChange($baseRun->memory, $treeRun->memory),
            ],
        ];

        $this->output->table($rows);
        $this->output->printLine('Report: %s', $this->file);
    }

    private function countValue(?int $count): string
    {
        return $count === null ? '-' : (string) $count;
    }

    private function countChange(?int $base, ?int $tree): string
    {
        if ($base === null || $tree === null) {
            return '-';
        }

        $change = $tree - $base;

        return $change === 0 ? '0' : sprintf('%+d', $change);
    }

    private function coverageChange(int $gained, int $missed, int $baseCovered, int $baseTotal, int $treeCovered, int $treeTotal): string
    {
        $change = $this->percentage($treeCovered, $treeTotal) - $this->percentage($baseCovered, $baseTotal);

        return sprintf('+%d / -%d (%+.2f%%)', $gained, $missed, $change);
    }

    private function coverageValue(int $covered, int $total): string
    {
        return sprintf('%d/%d (%.2f%%)', $covered, $total, $this->percentage($covered, $total));
    }

    private function formatMemory(?int $bytes, bool $signed = false): string
    {
        if ($bytes === null) {
            return '-';
        }

        $format = '%.1f MB';

        if ($signed === true) {
            $format = '%+.1f MB';
        }

        $megabytes = $bytes / 1048576;

        if (round($megabytes, 1) === 0.0) {
            $megabytes = 0.0;
        }

        return sprintf($format, $megabytes);
    }

    private function memoryChange(?int $base, ?int $tree): string
    {
        if ($base === null || $tree === null) {
            return '-';
        }

        return $this->formatMemory($tree - $base, true);
    }

    /**
     * @param list<string> $missedLines
     * @param list<string> $missedBranches
     * @param list<string> $gainedLines
     * @param list<string> $gainedBranches
     */
    private function writeCoverageReport(array $missedLines, array $missedBranches, array $gainedLines, array $gainedBranches): void
    {
        $groups = [
            'Missed' => ['Lines' => $missedLines, 'Branches' => $missedBranches],
            'Gained' => ['Lines' => $gainedLines, 'Branches' => $gainedBranches],
        ];

        $lines = [];

        foreach ($groups as $group => $sections) {

            $lines[] = $group;
            $lines[] = str_repeat('=', strlen($group));

            foreach ($sections as $section => $locations) {

                sort($locations, SORT_NATURAL);
                $heading = sprintf('%s (%d)', $section, count($locations));
                $lines[] = '';
                $lines[] = $heading;
                $lines[] = str_repeat('-', strlen($heading));

                if ($locations === []) {
                    $locations = ['None'];
                }

                $lines = [...$lines, ...$locations];
            }

            $lines[] = '';
        }

        $this->write($this->output->lines($lines));
    }

    private function write(string $content): void
    {
        if (file_put_contents($this->file, $content) === false) {
            throw new RuntimeException("Could not write coverage report: $this->file");
        }
    }

    private function percentage(int $covered, int $total): float
    {
        if ($total === 0) {
            return 100.0;
        }

        return 100 * $covered / $total;
    }
}
