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
    public function build(string $base, string $tree, array $paths): TestTrees
    {
        return new TestTrees($base, $tree, $this->tests($base, $tree, $paths));
    }

    /** @param list<string> $paths */
    private function tests(string $baseTree, string $tree, array $paths): PhptSuites
    {
        if ($paths === []) {
            return new PhptSuites(null, null);
        }

        $this->scope->validate($baseTree, $tree, $paths);

        $baseTests = $this->scope->files($baseTree, $paths);
        $treeTests = $this->scope->files($tree, $paths);

        if ($baseTests === [] && $treeTests === []) {
            throw new RuntimeException('Selected scope does not match any files');
        }

        return new PhptSuites($baseTests, $treeTests);
    }
}
