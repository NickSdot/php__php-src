<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

final class TestTreeBuilder
{
    public function __construct(
        private PhptScope $scope = new PhptScope()
    ) {}

    /** @param list<string> $paths */
    public function build(string $baseDirectory, string $treeDirectory, array $paths): TestTrees
    {
        return new TestTrees(
            $baseDirectory,
            $treeDirectory,
            $this->tests($baseDirectory, $treeDirectory, $paths)
        );
    }

    /** @param list<string> $paths */
    private function tests(string $baseDirectory, string $treeDirectory, array $paths): PhptSuites
    {
        if ($paths === []) {
            return new PhptSuites(null, null);
        }

        $this->scope->validate($baseDirectory, $treeDirectory, $paths);

        $baseTests = $this->scope->files($baseDirectory, $paths);
        $treeTests = $this->scope->files($treeDirectory, $paths);

        if ($baseTests === [] && $treeTests === []) {
            throw new RuntimeException('Selected scope does not match any files');
        }

        return new PhptSuites($baseTests, $treeTests);
    }
}
