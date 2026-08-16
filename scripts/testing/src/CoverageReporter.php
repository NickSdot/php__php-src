<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function count;
use function explode;
use function file_put_contents;
use function implode;
use function round;
use function sprintf;
use function str_repeat;
use function strlen;
use function wordwrap;

final class CoverageReporter
{
    private const REPORT_WIDTH = 80;
    private const SECTION_PREFIX = '=====';
    private const FINDING_PREFIX = '      ';

    public function __construct(
        private string $file,
        private Output $output = new Output()
    ) {
        $this->write($this->output->line('Coverage comparison did not complete.'));
    }

    public function report(CoverageComparisonResult $comparison, PhptRun $baseRun, PhptRun $treeRun, PhptChanges $testChanges): void
    {
        $missedLines = $comparison->missedLines();
        $missedBranches = $comparison->missedBranches();

        $gainedLines = $comparison->gainedLines();
        $gainedBranches = $comparison->gainedBranches();

        $this->writeCoverageReport($comparison, $testChanges);

        $rows = [
            ['', 'Tests', 'Sources', 'Lines', 'Branches', 'Time', 'Memory'],
            [
                'Base',
                (string) $baseRun->testCount(),
                (string) $comparison->baseSources(),
                $this->coverageValue($comparison->baseLines(), $comparison->baseExecutableLines()),
                $this->coverageValue($comparison->baseBranches(), $comparison->baseExecutableBranches()),
                sprintf('%.2fs', $baseRun->measurement->time),
                $this->formatMemory($baseRun->measurement->memory),
            ],
            [
                'Tree',
                (string) $treeRun->testCount(),
                (string) $comparison->treeSources(),
                $this->coverageValue($comparison->treeLines(), $comparison->treeExecutableLines()),
                $this->coverageValue($comparison->treeBranches(), $comparison->treeExecutableBranches()),
                sprintf('%.2fs', $treeRun->measurement->time),
                $this->formatMemory($treeRun->measurement->memory),
            ],
            [
                'Change',
                $this->countChange($baseRun->testCount(), $treeRun->testCount()),
                $this->countChange($comparison->baseSources(), $comparison->treeSources()),
                $this->coverageChange(
                    count($gainedLines),
                    count($missedLines),
                    $comparison->baseLines(),
                    $comparison->baseExecutableLines(),
                    $comparison->treeLines(),
                    $comparison->treeExecutableLines()
                ),
                $this->coverageChange(
                    count($gainedBranches),
                    count($missedBranches),
                    $comparison->baseBranches(),
                    $comparison->baseExecutableBranches(),
                    $comparison->treeBranches(),
                    $comparison->treeExecutableBranches()
                ),
                sprintf('%+.2fs', $treeRun->measurement->time - $baseRun->measurement->time),
                $this->memoryChange($baseRun->measurement->memory, $treeRun->measurement->memory),
            ],
        ];

        $this->output->table($rows);
        $this->output->printLine('Report: %s', $this->file);
    }

    private function countChange(int $base, int $tree): string
    {
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

    private function writeCoverageReport(CoverageComparisonResult $comparison, PhptChanges $testChanges): void
    {
        $groups = [
            'Missed' => [
                'Lines' => $comparison->missedLines(),
                'Branches' => $comparison->missedBranches(),
            ],
            'Gained' => [
                'Lines' => $comparison->gainedLines(),
                'Branches' => $comparison->gainedBranches(),
            ],
            'Uncovered' => [
                'Lines' => $comparison->uncoveredLines(),
                'Branches' => $comparison->uncoveredBranches(),
            ],
        ];

        $lines = [];

        foreach ($groups as $group => $sections) {

            $lines[] = $this->groupHeading($group);

            foreach ($sections as $section => $locations) {
                $this->addSection($lines, $section, count($locations), $this->coverageFindings($locations));
            }

            $lines[] = '';
        }

        $lines[] = $this->groupHeading('Tests');

        foreach ($this->testSections($testChanges) as $section => $findings) {
            $this->addSection($lines, $section, count($findings), $findings);
        }

        $lines[] = '';

        $this->write($this->output->lines($lines));
    }

    /**
     * @param list<string> $lines
     * @param list<string> $findings
     */
    private function addSection(array &$lines, string $section, int $count, array $findings): void
    {
        $lines[] = '';
        $lines[] = self::FINDING_PREFIX . sprintf('%s (%d)', $section, $count);
        $lines[] = self::FINDING_PREFIX . str_repeat('-', self::REPORT_WIDTH - strlen(self::FINDING_PREFIX));

        if ($findings === []) {
            $lines[] = self::FINDING_PREFIX . 'None';
            return;
        }

        foreach ($findings as $finding) {
            foreach (explode("\n", $finding) as $line) {
                $lines[] = self::FINDING_PREFIX . $line;
            }
        }
    }

    /** @return list<string> */
    private function coverageFindings(CoverageLocations $locations): array
    {
        $findings = [];

        foreach ($locations->bySource() as $source => $entries) {
            $findings[] = "$source:";
            $findings[] = '  ' . wordwrap(
                implode(' ', $entries),
                self::REPORT_WIDTH - strlen(self::FINDING_PREFIX) - 2,
                "\n  "
            );
        }

        return $findings;
    }

    /** @return array<string, list<string>> */
    private function testSections(PhptChanges $changes): array
    {
        $sections = [
            'Created' => [],
            'Deleted' => [],
            'Renamed' => [],
            'Skipped' => [],
        ];

        foreach ($changes->created() as $change) {
            $sections['Created'][] = $this->wrapTestFinding($change->path());
        }

        foreach ($changes->deleted() as $change) {
            $sections['Deleted'][] = $this->wrapTestFinding($change->path());
        }

        foreach ($changes->renamed() as $change) {
            $sections['Renamed'][] = $this->wrapTestFinding($this->testPath($change));
        }

        foreach ($changes->skipped() as $change) {
            $sections['Skipped'][] = $this->wrapTestFinding(sprintf(
                '%s: %s -> %s',
                $this->testPath($change),
                $change->baseStatus ?? '-',
                $change->treeStatus ?? '-'
            ));
        }

        return $sections;
    }

    private function testPath(PhptChange $change): string
    {
        if ($change->basePath !== null && $change->treePath !== null && $change->basePath !== $change->treePath) {
            return "{$change->basePath} -> {$change->treePath}";
        }

        return $change->path();
    }

    private function wrapTestFinding(string $finding): string
    {
        return wordwrap(
            $finding,
            self::REPORT_WIDTH - strlen(self::FINDING_PREFIX) - 2,
            "\n  "
        );
    }

    private function groupHeading(string $group): string
    {
        $prefix = self::SECTION_PREFIX . " $group ";

        return $prefix . str_repeat('=', self::REPORT_WIDTH - strlen($prefix));
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
