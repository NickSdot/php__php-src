<?php

require_once __DIR__ . '/PhptFile.php';
require_once __DIR__ . '/TestStyleFixer.php';

abstract class TestStyleFixerBase implements TestStyleFixer
{
    protected PhptFile $file;
    /** @var list<int> */
    private array $lines = [];
    private ?string $failureReason = null;
    private ?string $expectedUpdateFailure = null;

    public function __construct(PhptFile $file)
    {
        $this->file = $file;
    }

    final public function persist(): bool
    {
        if (!$this->isCollected()) {
            return $this->fail('internal error: collect() did not prepare a rewrite');
        }

        $initial = $this->file->run();
        if ($initial['status'] !== 'PASS') {
            return $this->fail('original test did not pass (' . $this->runSummary($initial) . ')');
        }

        $this->apply();
        $this->file->save();

        $run = $this->file->run();
        if ($run['status'] === 'PASS') {
            $this->file->cleanupArtifacts();
            return true;
        }

        if (!$this->changesOutput()) {
            return $this->fail('rewritten test did not pass after a style-only rewrite (' . $this->runSummary($run) . ')');
        }

        $actual = $this->file->readActualOutput();
        if ($actual === null) {
            return $this->fail('rewritten test did not pass and no .out file was produced (' . $this->runSummary($run) . ')');
        }

        $expectedSection = $this->file->expectedSectionName();
        if ($expectedSection === null) {
            return $this->fail('no expected output section is available for update');
        }

        $expected = $this->file->getSection($expectedSection);
        if ($expected === null) {
            return $this->fail("$expectedSection section disappeared while updating expected output");
        }

        $this->expectedUpdateFailure = null;
        $updated = $this->updateExpectedOutput($expectedSection, $expected, $actual);
        if ($updated === null) {
            return $this->fail(
                "$expectedSection update was not provable after rewritten test failed ("
                    . $this->runSummary($run) . '): '
                    . ($this->expectedUpdateFailure ?? 'actual output was not a safe expected-output rewrite')
            );
        }

        $this->file->setSection($expectedSection, $updated);
        $this->file->save();

        $verified = $this->file->run();
        if ($verified['status'] !== 'PASS') {
            return $this->fail('updated expected output did not pass verification (' . $this->runSummary($verified) . ')');
        }

        $this->file->cleanupArtifacts();
        return true;
    }

    public function location(): string
    {
        if ($this->lines === []) {
            return '';
        }

        $visibleLines = array_slice($this->lines, 0, 5);
        $label = count($this->lines) === 1 ? 'FILE line ' : 'FILE lines ';
        $location = $label . implode(', ', $visibleLines);
        if (count($this->lines) > count($visibleLines)) {
            $location .= ' +' . (count($this->lines) - count($visibleLines)) . ' more';
        }

        return $location;
    }

    public function failureReason(): string
    {
        return $this->failureReason ?? 'unknown reason';
    }

    abstract protected function isCollected(): bool;

    abstract protected function apply(): void;

    protected function changesOutput(): bool
    {
        return false;
    }

    protected function updateExpectedOutput(string $section, string $expected, string $actual): ?string
    {
        $this->setExpectedUpdateFailure("fixer does not support updating $section");
        return null;
    }

    protected function resetDiagnostics(): void
    {
        $this->lines = [];
        $this->failureReason = null;
        $this->expectedUpdateFailure = null;
    }

    protected function markLineForOffset(string $code, int $offset): void
    {
        $this->markLine(substr_count(substr($code, 0, $offset), "\n") + 1);
    }

    protected function markLine(int $line): void
    {
        $this->lines[] = $line;
        sort($this->lines);
        $this->lines = array_values(array_unique($this->lines));
    }

    protected function setExpectedUpdateFailure(string $reason): void
    {
        $this->expectedUpdateFailure = $reason;
    }

    protected function fail(string $reason): bool
    {
        $this->failureReason = $reason;
        return false;
    }

    /**
     * @param array{status: string, output: string, exitCode: int} $run
     */
    protected function runSummary(array $run): string
    {
        $status = $run['status'];
        if (preg_match('/^SKIP .* reason: (.+)$/m', $run['output'], $matches)) {
            return $status . ': ' . $this->shorten(trim($matches[1]));
        }
        if (preg_match('/^(PASS|SKIP|FAIL|BORK|WARN|XFAIL|XLEAK|LEAK) .+$/m', $run['output'], $matches)) {
            return $this->shorten(trim($matches[0]));
        }

        foreach (preg_split('/\R/', $run['output']) as $line) {
            $line = trim($line);
            if ($line !== '' && !str_starts_with($line, '=')) {
                return $status . ': ' . $this->shorten($line);
            }
        }

        return $status . ', exit code ' . $run['exitCode'];
    }

    protected function shorten(string $text): string
    {
        $text = str_replace(["\r", "\n", "\t"], [' ', ' ', ' '], $text);
        if (strlen($text) <= 180) {
            return $text;
        }

        return substr($text, 0, 177) . '...';
    }
}
