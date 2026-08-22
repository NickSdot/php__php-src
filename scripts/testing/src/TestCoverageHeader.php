<?php

declare(strict_types=1);

namespace PHP\Testing;

use function array_map;
use function count;
use function implode;
use function sprintf;

final readonly class TestCoverageHeader
{
    public function __construct(
        private TestCoverageOptions $options,
        private string $baseRevision,
        private string $treeRevision
    ) {}

    /** @return list<string> */
    public function lines(): array
    {
        return [
            ...$this->coverageLines(),
            '',
            sprintf('Base: %s %s', $this->baseRevision, $this->options->base),
            sprintf('Tree: %s %s', $this->treeRevision, $this->options->tree ?? 'working tree'),
            '',
        ];
    }

    /** @return non-empty-list<string> */
    private function coverageLines(): array
    {
        $selections = $this->selections();

        if (count($selections) === 1) {
            return ["Coverage: $selections[0]"];
        }

        return [
            'Coverage:',
            ...array_map(static fn(string $selection): string => "  $selection", $selections),
        ];
    }

    /** @return non-empty-list<string> */
    private function selections(): array
    {
        $selections = match (true) {
            $this->options->global === true => ['global'],
            $this->options->sources !== [] => [implode(', ', $this->options->sources)],
            default => [],
        };

        if ($this->options->testPaths !== []) {
            $selections[] = implode(', ', $this->options->testPaths);
        }

        if ($selections === []) {
            $selections[] = 'global';
        }

        return $selections;
    }
}
