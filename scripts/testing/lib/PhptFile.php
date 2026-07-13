<?php

class PhptFile
{
    private string $path;
    private string $rootDir;
    private string $originalContents;
    private string $prefix = '';
    /** @var array<int, array{name: string, header: string, content: string}> */
    private array $sections = [];

    public function __construct(string $path, string $rootDir)
    {
        $this->path = $path;
        $this->rootDir = $rootDir;
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("Cannot read $path");
        }
        $this->originalContents = $contents;
        $this->parse($contents);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function relativePath(): string
    {
        if (str_starts_with($this->path, $this->rootDir . DIRECTORY_SEPARATOR)) {
            return substr($this->path, strlen($this->rootDir) + 1);
        }
        return $this->path;
    }

    public function hasSection(string $name): bool
    {
        foreach ($this->sections as $section) {
            if ($section['name'] === $name) {
                return true;
            }
        }
        return false;
    }

    public function getSection(string $name): ?string
    {
        foreach ($this->sections as $section) {
            if ($section['name'] === $name) {
                return $section['content'];
            }
        }
        return null;
    }

    public function setSection(string $name, string $content): void
    {
        foreach ($this->sections as $i => $section) {
            if ($section['name'] === $name) {
                $this->sections[$i]['content'] = $content;
                return;
            }
        }
        throw new RuntimeException("Section $name not found in {$this->path}");
    }

    public function renameSection(string $oldName, string $newName): void
    {
        foreach ($this->sections as $i => $section) {
            if ($section['name'] === $oldName) {
                $lineEnding = str_contains($section['header'], "\r\n") ? "\r\n" : "\n";
                $this->sections[$i]['name'] = $newName;
                $this->sections[$i]['header'] = "--$newName--$lineEnding";
                return;
            }
        }
        throw new RuntimeException("Section $oldName not found in {$this->path}");
    }

    public function codeSectionName(): ?string
    {
        if ($this->hasSection('FILE')) {
            return 'FILE';
        }
        if ($this->hasSection('FILEEOF')) {
            return 'FILEEOF';
        }
        return null;
    }

    public function expectedSectionName(): ?string
    {
        foreach (['EXPECT', 'EXPECTF', 'EXPECTREGEX'] as $name) {
            if ($this->hasSection($name)) {
                return $name;
            }
        }
        return null;
    }

    public function setExpectedOutput(string $output): void
    {
        $section = $this->expectedSectionName();
        if ($section === null) {
            throw new RuntimeException("No expected output section in {$this->path}");
        }

        if ($section !== 'EXPECT') {
            $this->renameSection($section, 'EXPECT');
        }

        $output = str_replace(["\r\n", "\r"], "\n", $output);
        if ($output !== '' && !str_ends_with($output, "\n")) {
            $output .= "\n";
        }
        $this->setSection('EXPECT', $output);
    }

    public function save(): void
    {
        if (file_put_contents($this->path, $this->render()) === false) {
            throw new RuntimeException("Cannot write {$this->path}");
        }
    }

    public function restoreOriginal(): void
    {
        if (file_put_contents($this->path, $this->originalContents) === false) {
            throw new RuntimeException("Cannot restore {$this->path}");
        }
        $this->parse($this->originalContents);
    }

    public function contents(): string
    {
        return $this->render();
    }

    /**
     * @return array{status: string, output: string, exitCode: int}
     */
    public function run(): array
    {
        $this->cleanupArtifacts();

        $cmd = [
            $this->testPhpBinary(),
            'run-tests.php',
            '-q',
            '--no-progress',
            '--no-color',
            $this->relativePath(),
        ];
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $env = $_ENV;
        $env['NO_INTERACTION'] = '1';
        $env['REPORT_EXIT_STATUS'] = '1';

        $process = proc_open($cmd, $descriptorSpec, $pipes, $this->rootDir, $env);
        if (!is_resource($process)) {
            throw new RuntimeException("Cannot run {$this->relativePath()}");
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        $output = (string) $stdout . (string) $stderr;
        $status = 'unknown';
        if (preg_match('/^(PASS|SKIP|FAIL|BORK|WARN|XFAIL|XLEAK|LEAK) /m', $output, $matches)) {
            $status = $matches[1];
        }

        return [
            'status' => $status,
            'output' => $output,
            'exitCode' => $exitCode,
        ];
    }

    public function readActualOutput(): ?string
    {
        $path = $this->artifactPath('out');
        if (!is_file($path)) {
            return null;
        }
        $contents = file_get_contents($path);
        return $contents === false ? null : $contents;
    }

    public function cleanupArtifacts(): void
    {
        $base = $this->artifactBase();
        foreach (['diff', 'exp', 'log', 'mem', 'out', 'php', 'sh', 'skip.php', 'clean.php'] as $extension) {
            $path = "$base.$extension";
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function parse(string $contents): void
    {
        $this->sections = [];
        if (!preg_match_all('/^--([_A-Z]+)--[ \t]*(?:\r\n|\n|\r|$)/m', $contents, $matches, PREG_OFFSET_CAPTURE)) {
            $this->prefix = $contents;
            return;
        }

        $this->prefix = substr($contents, 0, $matches[0][0][1]);
        $count = count($matches[0]);
        for ($i = 0; $i < $count; $i++) {
            $header = $matches[0][$i][0];
            $name = $matches[1][$i][0];
            $contentStart = $matches[0][$i][1] + strlen($header);
            $contentEnd = $i + 1 < $count ? $matches[0][$i + 1][1] : strlen($contents);
            $this->sections[] = [
                'name' => $name,
                'header' => $header,
                'content' => substr($contents, $contentStart, $contentEnd - $contentStart),
            ];
        }
    }

    private function render(): string
    {
        $contents = $this->prefix;
        foreach ($this->sections as $section) {
            $contents .= $section['header'] . $section['content'];
        }
        return $contents;
    }

    private function artifactBase(): string
    {
        $pathInfo = pathinfo($this->path);
        return $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'];
    }

    private function artifactPath(string $extension): string
    {
        return $this->artifactBase() . ".$extension";
    }

    private function testPhpBinary(): string
    {
        $binary = $this->rootDir . DIRECTORY_SEPARATOR . 'sapi' . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'php';
        if (is_file($binary) && is_executable($binary)) {
            return $binary;
        }

        return PHP_BINARY;
    }
}
