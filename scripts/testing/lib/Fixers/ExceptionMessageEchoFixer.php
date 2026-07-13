<?php

require_once __DIR__ . '/../TestStyleFixerBase.php';

class ExceptionMessageEchoFixer extends TestStyleFixerBase
{
    private const NEEDLE = 'getMessage';

    private ?string $codeSection = null;
    private ?string $newCode = null;
    private bool $changesOutput = false;

    public function name(): string
    {
        return 'exception-message-echo';
    }

    public function collect(): bool
    {
        $this->resetDiagnostics();
        $this->codeSection = null;
        $this->newCode = null;
        $this->changesOutput = false;

        $this->codeSection = $this->file->codeSectionName();
        if ($this->codeSection === null || $this->file->hasSection('XFAIL')) {
            return false;
        }

        $code = $this->file->getSection($this->codeSection);
        if ($code === null || !str_contains($code, self::NEEDLE)) {
            return false;
        }

        $tokens = $this->tokenize($code);
        $edits = [];

        foreach ($this->collectCatchBlocks($tokens) as $catch) {
            $nestedRanges = $this->collectNestedDeclarationRanges($tokens, $catch['bodyStart'], $catch['bodyEnd']);

            for ($i = $catch['bodyStart'] + 1; $i < $catch['bodyEnd']; $i++) {
                if (
                    !$this->isSupportedOutputStatementStart($tokens, $i)
                    || $this->isInsideRanges($i, $nestedRanges)
                ) {
                    continue;
                }
                if ($this->catchVariableWasReassigned($tokens, $catch['var'], $catch['bodyStart'], $i)) {
                    continue;
                }

                $statementEnd = $this->findStatementEnd($tokens, $i);
                if ($statementEnd === null || $statementEnd > $catch['bodyEnd']) {
                    continue;
                }

                $match = $this->matchOutputStatement($code, $tokens, $i, $statementEnd, $catch['var']);
                if ($match === null) {
                    continue;
                }

                $start = $tokens[$i]['offset'];
                $end = $tokens[$statementEnd]['offset'] + strlen($tokens[$statementEnd]['text']);
                $current = substr($code, $start, $end - $start);

                if (isset($match['replacement'])) {
                    $replacement = $match['replacement'];
                } elseif (isset($match['messageRange'])) {
                    $messageStart = $tokens[$match['messageRange']['start']]['offset'];
                    $messageEnd = $tokens[$match['messageRange']['end']]['offset'] + strlen($tokens[$match['messageRange']['end']]['text']);
                    $message = substr($code, $messageStart, $messageEnd - $messageStart);
                    $replacement = substr($code, $start, $messageStart - $start)
                        . $catch['var'] . '::class . ": " . ' . $message
                        . substr($code, $messageEnd, $end - $messageEnd);
                } else {
                    $replacement = 'echo ' . $catch['var'] . '::class, ": ", ' . $catch['var'] . '->getMessage()'
                        . ($match['hasNewline'] ? ', "\n"' : '')
                        . ';';
                }
                if ($current === $replacement) {
                    $i = $statementEnd;
                    continue;
                }

                $edits[] = [
                    'start' => $start,
                    'end' => $end,
                    'replacement' => $replacement,
                ];
                $this->markLineForOffset($code, $start);
                $this->changesOutput = $this->changesOutput || $match['changesOutput'];
                $i = $statementEnd;
            }
        }

        if ($edits === []) {
            return false;
        }

        usort($edits, static fn(array $a, array $b): int => $b['start'] <=> $a['start']);
        foreach ($edits as $edit) {
            $code = substr($code, 0, $edit['start']) . $edit['replacement'] . substr($code, $edit['end']);
        }

        $this->newCode = $code;
        return true;
    }

    protected function isCollected(): bool
    {
        return $this->codeSection !== null && $this->newCode !== null;
    }

    protected function apply(): void
    {
        $this->file->setSection($this->codeSection, $this->newCode);
    }

    protected function changesOutput(): bool
    {
        return $this->changesOutput;
    }

    protected function updateExpectedOutput(string $section, string $expected, string $actual): ?string
    {
        if ($section === 'EXPECT') {
            return $this->updateExpect($expected, $actual);
        }
        if ($section === 'EXPECTF') {
            return $this->updateExpectf($expected, $actual);
        }

        $this->setExpectedUpdateFailure("section $section is not supported by this fixer");
        return null;
    }

    private function updateExpect(string $expected, string $actual): ?string
    {
        $expectedLines = $this->normalizeOutputLines($expected);
        $actualLines = $this->normalizeOutputLines($actual);
        if (count($expectedLines) !== count($actualLines)) {
            $this->setExpectedUpdateFailure('line count changed from ' . count($expectedLines) . ' to ' . count($actualLines));
            return null;
        }

        $updated = [];
        foreach ($expectedLines as $i => $expectedLine) {
            $actualLine = $actualLines[$i];
            if ($expectedLine === $actualLine) {
                $updated[] = $expectedLine;
                continue;
            }
            if ($this->actualLineMatchesExpectedWithInsertedOrReplacedClass($expectedLine, $actualLine)) {
                $updated[] = $actualLine;
                continue;
            }
            if ($this->actualLineMatchesExpectedVarDumpStringRewrite($expectedLine, $actualLine)) {
                $updated[] = $actualLine;
                continue;
            }
            if ($this->actualLineMatchesExpectedVarDumpLiteralRewrite($expectedLine, $actualLine)) {
                $updated[] = $actualLine;
                continue;
            }
            if ($this->actualLineMatchesExpectedClassAwareRewrite($expectedLine, $actualLine)) {
                $updated[] = $actualLine;
                continue;
            }
            $this->setExpectedUpdateFailure(
                'line ' . ($i + 1) . ' did not change only by inserting an exception class prefix'
                    . '; expected "' . $this->shorten($expectedLine) . '", actual "' . $this->shorten($actualLine) . '"'
            );
            return null;
        }

        return implode("\n", $updated) . "\n";
    }

    private function updateExpectf(string $expected, string $actual): ?string
    {
        $expectedLines = $this->normalizeOutputLines($expected);
        $actualLines = $this->normalizeOutputLines($actual);
        if (count($expectedLines) !== count($actualLines)) {
            $this->setExpectedUpdateFailure('line count changed from ' . count($expectedLines) . ' to ' . count($actualLines));
            return null;
        }

        $updated = [];
        foreach ($expectedLines as $i => $expectedLine) {
            $actualLine = $actualLines[$i];
            if ($this->expectfLineMatches($expectedLine, $actualLine)) {
                $updated[] = $expectedLine;
                continue;
            }

            $updatedLine = $this->insertClassPrefixIntoExpectfLine($expectedLine, $actualLine);
            if ($updatedLine !== null) {
                $updated[] = $updatedLine;
                continue;
            }

            $updatedLine = $this->rewriteVarDumpStringExpectfLine($expectedLine, $actualLine);
            if ($updatedLine !== null) {
                $updated[] = $updatedLine;
                continue;
            }

            $updatedLine = $this->rewriteVarDumpLiteralExpectfLine($expectedLine, $actualLine);
            if ($updatedLine !== null) {
                $updated[] = $updatedLine;
                continue;
            }

            $updatedLine = $this->rewriteClassAwareExpectfLine($expectedLine, $actualLine);
            if ($updatedLine !== null) {
                $updated[] = $updatedLine;
                continue;
            }

            $this->setExpectedUpdateFailure(
                'line ' . ($i + 1) . ' did not match after inserting an exception class prefix'
                    . '; expected pattern "' . $this->shorten($expectedLine) . '", actual "' . $this->shorten($actualLine) . '"'
            );
            return null;
        }

        return implode("\n", $updated) . "\n";
    }

    /**
     * @return list<string>
     */
    private function normalizeOutputLines(string $output): array
    {
        $lines = explode("\n", rtrim(str_replace(["\r\n", "\r"], "\n", $output), "\n"));
        return $lines === [''] ? [] : $lines;
    }

    private function actualLineMatchesExpectedWithInsertedOrReplacedClass(string $expectedLine, string $actualLine): bool
    {
        if (!preg_match_all('/([A-Za-z_\\\\][A-Za-z0-9_\\\\]*): /', $actualLine, $matches, PREG_OFFSET_CAPTURE)) {
            return false;
        }

        foreach ($matches[0] as $match) {
            $offset = $match[1];
            $length = strlen($match[0]);
            if (substr($actualLine, 0, $offset) . substr($actualLine, $offset + $length) === $expectedLine) {
                return true;
            }
            $expectedPrefixLength = $this->classPrefixLengthAt($expectedLine, $offset);
            if (
                $expectedPrefixLength !== null
                && substr($expectedLine, 0, $offset) === substr($actualLine, 0, $offset)
                && substr($expectedLine, $offset + $expectedPrefixLength) === substr($actualLine, $offset + $length)
            ) {
                return true;
            }
        }

        return false;
    }

    private function actualLineMatchesExpectedVarDumpStringRewrite(string $expectedLine, string $actualLine): bool
    {
        $message = $this->varDumpStringMessage($expectedLine);
        if ($message === null) {
            return false;
        }

        $prefixLength = $this->classPrefixLengthAt($actualLine, 0);
        return $prefixLength !== null && substr($actualLine, $prefixLength) === $message;
    }

    private function actualLineMatchesExpectedVarDumpLiteralRewrite(string $expectedLine, string $actualLine): bool
    {
        return $this->varDumpStringMessage($expectedLine) === $actualLine;
    }

    private function actualLineMatchesExpectedClassAwareRewrite(string $expectedLine, string $actualLine): bool
    {
        $actual = $this->actualClassAndMessage($actualLine);
        if ($actual === null) {
            return false;
        }

        [$class, $message] = $actual;
        foreach ($this->classAwareExpectedLineCandidates($class, $message) as $candidate) {
            if ($expectedLine === $candidate) {
                return true;
            }
        }

        return false;
    }

    private function insertClassPrefixIntoExpectfLine(string $expectedLine, string $actualLine): ?string
    {
        if (!preg_match_all('/([A-Za-z_\\\\][A-Za-z0-9_\\\\]*): /', $actualLine, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        foreach ($matches[0] as $match) {
            if (!$this->isClassPrefix($match[0])) {
                continue;
            }

            $offset = $match[1];
            $length = strlen($match[0]);
            $actualWithoutPrefix = substr($actualLine, 0, $offset) . substr($actualLine, $offset + $length);
            if (!$this->expectfLineMatches($expectedLine, $actualWithoutPrefix)) {
                continue;
            }

            $literalPrefix = substr($actualLine, 0, $offset);
            if (!str_starts_with($expectedLine, $literalPrefix)) {
                continue;
            }

            return $literalPrefix . $match[0] . substr($expectedLine, strlen($literalPrefix));
        }

        foreach ($matches[0] as $match) {
            if (!$this->isClassPrefix($match[0])) {
                continue;
            }

            $offset = $match[1];
            $literalPrefix = substr($actualLine, 0, $offset);
            if (!str_starts_with($expectedLine, $literalPrefix)) {
                continue;
            }

            $expectedPrefixLength = $this->classPrefixLengthAt($expectedLine, strlen($literalPrefix));
            if ($expectedPrefixLength === null) {
                continue;
            }

            $updatedLine = $literalPrefix . $match[0] . substr($expectedLine, strlen($literalPrefix) + $expectedPrefixLength);
            if ($this->expectfLineMatches($updatedLine, $actualLine)) {
                return $updatedLine;
            }
        }

        return null;
    }

    private function rewriteVarDumpStringExpectfLine(string $expectedLine, string $actualLine): ?string
    {
        $messagePattern = $this->varDumpStringMessage($expectedLine);
        if ($messagePattern === null) {
            return null;
        }

        $prefixLength = $this->classPrefixLengthAt($actualLine, 0);
        if ($prefixLength === null) {
            return null;
        }

        $updatedLine = substr($actualLine, 0, $prefixLength) . $messagePattern;
        return $this->expectfLineMatches($updatedLine, $actualLine) ? $updatedLine : null;
    }

    private function rewriteVarDumpLiteralExpectfLine(string $expectedLine, string $actualLine): ?string
    {
        $messagePattern = $this->varDumpStringMessage($expectedLine);
        if ($messagePattern === null) {
            return null;
        }

        return $this->expectfLineMatches($messagePattern, $actualLine) ? $messagePattern : null;
    }

    private function rewriteClassAwareExpectfLine(string $expectedLine, string $actualLine): ?string
    {
        $actual = $this->actualClassAndMessage($actualLine);
        if ($actual === null) {
            return null;
        }

        [$class, $message] = $actual;
        foreach ($this->classAwareExpectedLineCandidates($class, $message) as $candidate) {
            if (!$this->expectfLineMatches($expectedLine, $candidate)) {
                continue;
            }

            $updatedLine = $this->rewriteClassAwareExpectedPattern($expectedLine, $class);
            if ($updatedLine !== null && $this->expectfLineMatches($updatedLine, $actualLine)) {
                return $updatedLine;
            }

            return $actualLine;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function classAwareExpectedLineCandidates(string $class, string $message): array
    {
        return [
            'Exception ' . $message,
            'Error ' . $message,
            $class . ' : ' . $message,
            'Exception (' . $class . '): ' . $message,
            '[' . $class . '] ' . $message,
            'Caught ' . $class . '(' . $message . ')',
            $class . $message,
            $class . ": '" . $message . "'",
        ];
    }

    private function rewriteClassAwareExpectedPattern(string $expectedLine, string $class): ?string
    {
        $quotedClass = preg_quote($class, '/');
        $classPattern = '(?:' . $quotedClass . '|%s|%S)';

        if (preg_match('/^Exception \((' . $classPattern . ')\): (.*)$/', $expectedLine, $matches)) {
            return $matches[1] . ': ' . $matches[2];
        }
        if (preg_match('/^\[(' . $classPattern . ')\] (.*)$/', $expectedLine, $matches)) {
            return $matches[1] . ': ' . $matches[2];
        }
        if (preg_match('/^Caught (' . $classPattern . ')\((.*)\)$/', $expectedLine, $matches)) {
            return $matches[1] . ': ' . $matches[2];
        }
        if (preg_match('/^(' . $classPattern . ') : (.*)$/', $expectedLine, $matches)) {
            return $matches[1] . ': ' . $matches[2];
        }
        if (preg_match('/^(' . $classPattern . "): '(.*)'$/", $expectedLine, $matches)) {
            return $matches[1] . ': ' . $matches[2];
        }
        if (preg_match('/^(?:Exception|Error) (.*)$/', $expectedLine, $matches)) {
            return $class . ': ' . $matches[1];
        }

        return null;
    }

    private function varDumpStringMessage(string $line): ?string
    {
        if (!preg_match('/^string\((?:\d+|%d)\) "(.*)"$/', $line, $matches)) {
            return null;
        }

        return stripcslashes($matches[1]);
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private function actualClassAndMessage(string $actualLine): ?array
    {
        $prefixLength = $this->classPrefixLengthAt($actualLine, 0);
        if ($prefixLength !== null) {
            return [
                substr($actualLine, 0, $prefixLength - 2),
                substr($actualLine, $prefixLength),
            ];
        }

        if (preg_match('/^([A-Za-z_\\\\][A-Za-z0-9_\\\\]*):$/', $actualLine, $matches) === 1) {
            $classPrefix = $matches[1] . ': ';
            if ($this->isClassPrefix($classPrefix)) {
                return [$matches[1], ''];
            }
        }

        return null;
    }

    private function expectfLineMatches(string $expected, string $actual): bool
    {
        return preg_match('/^' . $this->expectfToRegex($expected) . '$/s', $actual) === 1;
    }

    private function expectfToRegex(string $expected): string
    {
        $regex = '';
        $length = strlen($expected);

        for ($i = 0; $i < $length; $i++) {
            if ($expected[$i] !== '%' || $i + 1 >= $length) {
                $regex .= preg_quote($expected[$i], '/');
                continue;
            }

            $placeholder = $expected[++$i];
            $regex .= match ($placeholder) {
                'e' => preg_quote(DIRECTORY_SEPARATOR, '/'),
                's' => '[^\r\n]+',
                'S' => '[^\r\n]*',
                'a' => '.+',
                'A' => '.*',
                'w' => '\s*',
                'i' => '[+-]?\d+',
                'd' => '\d+',
                'x' => '[0-9a-fA-F]+',
                'f' => '[+-]?\.?\d+\.?\d*(?:[Ee][+-]?\d+)?',
                'c' => '.',
                '0' => '\0',
                default => preg_quote('%' . $placeholder, '/'),
            };
        }

        return $regex;
    }

    /**
     * @return list<array{id: int|null, text: string, offset: int}>
     */
    private function tokenize(string $code): array
    {
        $tokens = [];
        $offset = 0;

        foreach (token_get_all($code) as $token) {
            if (is_array($token)) {
                $text = $token[1];
                $tokens[] = [
                    'id' => $token[0],
                    'text' => $text,
                    'offset' => $offset,
                ];
                $offset += strlen($text);
                continue;
            }

            $tokens[] = [
                'id' => null,
                'text' => $token,
                'offset' => $offset,
            ];
            $offset += strlen($token);
        }

        return $tokens;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @return list<array{var: string, bodyStart: int, bodyEnd: int}>
     */
    private function collectCatchBlocks(array $tokens): array
    {
        $blocks = [];
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if (($tokens[$i]['id'] ?? null) !== T_CATCH) {
                continue;
            }

            $openParen = $this->findNextText($tokens, '(', $i + 1);
            if ($openParen === null) {
                continue;
            }

            $closeParen = $this->findMatching($tokens, $openParen);
            if ($closeParen === null) {
                continue;
            }

            $var = null;
            for ($j = $openParen + 1; $j < $closeParen; $j++) {
                if (($tokens[$j]['id'] ?? null) === T_VARIABLE) {
                    $var = $tokens[$j]['text'];
                }
            }
            if ($var === null) {
                continue;
            }

            $bodyStart = $this->findNextText($tokens, '{', $closeParen + 1);
            if ($bodyStart === null) {
                continue;
            }

            $bodyEnd = $this->findMatching($tokens, $bodyStart);
            if ($bodyEnd === null) {
                continue;
            }

            $blocks[] = [
                'var' => $var,
                'bodyStart' => $bodyStart,
                'bodyEnd' => $bodyEnd,
            ];
            $i = $bodyEnd;
        }

        return $blocks;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @return list<array{start: int, end: int}>
     */
    private function collectNestedDeclarationRanges(array $tokens, int $bodyStart, int $bodyEnd): array
    {
        $ranges = [];

        for ($i = $bodyStart + 1; $i < $bodyEnd; $i++) {
            $id = $tokens[$i]['id'] ?? null;
            if (!in_array($id, [T_CLASS, T_INTERFACE, T_TRAIT, T_FUNCTION], true)) {
                continue;
            }
            if ($id === T_CLASS) {
                $previous = $this->previousSignificant($tokens, $i - 1);
                if ($previous !== null && ($tokens[$previous]['id'] ?? null) === T_DOUBLE_COLON) {
                    continue;
                }
            }

            $openBrace = $this->findNextText($tokens, '{', $i + 1, $bodyEnd);
            if ($openBrace === null) {
                continue;
            }

            $closeBrace = $this->findMatching($tokens, $openBrace);
            if ($closeBrace === null || $closeBrace > $bodyEnd) {
                continue;
            }

            $ranges[] = [
                'start' => $i,
                'end' => $closeBrace,
            ];
            $i = $closeBrace;
        }

        return $ranges;
    }

    /**
     * @param list<array{start: int, end: int}> $ranges
     */
    private function isInsideRanges(int $index, array $ranges): bool
    {
        foreach ($ranges as $range) {
            if ($index >= $range['start'] && $index <= $range['end']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     */
    private function catchVariableWasReassigned(array $tokens, string $var, int $from, int $to): bool
    {
        for ($i = $from + 1; $i < $to; $i++) {
            if (($tokens[$i]['id'] ?? null) === T_UNSET) {
                $openParen = $this->findNextText($tokens, '(', $i + 1, $to);
                if ($openParen !== null) {
                    $closeParen = $this->findMatching($tokens, $openParen);
                    if ($closeParen !== null && $closeParen <= $to) {
                        for ($j = $openParen + 1; $j < $closeParen; $j++) {
                            if (($tokens[$j]['id'] ?? null) === T_VARIABLE && $tokens[$j]['text'] === $var) {
                                return true;
                            }
                        }
                    }
                }
            }

            if (($tokens[$i]['id'] ?? null) !== T_VARIABLE || $tokens[$i]['text'] !== $var) {
                continue;
            }

            $previous = $this->previousSignificant($tokens, $i - 1);
            $next = $this->nextSignificant($tokens, $i + 1);
            $previousText = $previous === null ? null : strtolower($tokens[$previous]['text']);
            $nextText = $next === null ? null : strtolower($tokens[$next]['text']);

            if (in_array($nextText, [
                '=',
                '+=',
                '-=',
                '*=',
                '/=',
                '.=',
                '%=',
                '&=',
                '|=',
                '^=',
                '<<=',
                '>>=',
                '??=',
                '++',
                '--',
            ], true)) {
                return true;
            }
            if (in_array($previousText, ['++', '--', 'as'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     */
    private function findStatementEnd(array $tokens, int $echoIndex): ?int
    {
        $paren = 0;
        $bracket = 0;
        $brace = 0;
        $count = count($tokens);

        for ($i = $echoIndex + 1; $i < $count; $i++) {
            $id = $tokens[$i]['id'] ?? null;
            if ($id !== null && $id !== T_CURLY_OPEN) {
                continue;
            }

            $text = $tokens[$i]['text'];
            if ($text === '(') {
                $paren++;
                continue;
            }
            if ($text === ')') {
                $paren--;
                continue;
            }
            if ($text === '[') {
                $bracket++;
                continue;
            }
            if ($text === ']') {
                $bracket--;
                continue;
            }
            if ($text === '{') {
                $brace++;
                continue;
            }
            if ($text === '}') {
                if ($brace === 0) {
                    return null;
                }
                $brace--;
                continue;
            }
            if ($text === ';' && $paren === 0 && $bracket === 0 && $brace === 0) {
                return $i;
            }
        }

        return null;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @return array{hasNewline?: bool, changesOutput: bool, messageRange?: array{start: int, end: int}, replacement?: string}|null
     */
    private function matchOutputStatement(string $code, array $tokens, int $statementStart, int $statementEnd, string $var): ?array
    {
        if (($tokens[$statementStart]['id'] ?? null) === T_STRING && strtolower($tokens[$statementStart]['text']) === 'var_dump') {
            return $this->matchVarDumpStatement($tokens, $statementStart, $statementEnd, $var);
        }

        return $this->matchEchoStatement($code, $tokens, $statementStart, $statementEnd, $var);
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @return array{changesOutput: bool, replacement: string}|null
     */
    private function matchVarDumpStatement(array $tokens, int $start, int $statementEnd, string $var): ?array
    {
        $openParen = $this->nextSignificant($tokens, $start + 1);
        if ($openParen === null || $tokens[$openParen]['text'] !== '(') {
            return null;
        }

        $closeParen = $this->findMatching($tokens, $openParen);
        if ($closeParen === null) {
            return null;
        }

        $next = $this->nextSignificant($tokens, $closeParen + 1);
        if ($next !== $statementEnd) {
            return null;
        }

        $args = $this->parseEchoItems($tokens, $openParen + 1, $closeParen);
        if ($args === null) {
            return null;
        }

        if (count($args) === 1 && $this->matchesMessage($tokens, $args[0], $var)) {
            return [
                'changesOutput' => true,
                'replacement' => 'echo ' . $var . '::class, ": ", ' . $var . '->getMessage(), "\n";',
            ];
        }

        if (count($args) === 2 && $this->matchesMessage($tokens, $args[1], $var)) {
            $label = $this->decodeConstantString($tokens, $args[0]);
            if ($label !== null) {
                return [
                    'changesOutput' => true,
                    'replacement' => 'echo ' . $this->quoteString($label) . ', "\n", '
                        . $var . '::class, ": ", ' . $var . '->getMessage(), "\n";',
                ];
            }
        }

        return null;
    }
    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @return array{hasNewline?: bool, changesOutput: bool, messageRange?: array{start: int, end: int}, replacement?: string}|null
     */
    private function matchEchoStatement(string $code, array $tokens, int $echoIndex, int $statementEnd, string $var): ?array
    {
        $items = $this->parseEchoItems($tokens, $echoIndex + 1, $statementEnd);
        if ($items === null) {
            return null;
        }

        if (count($items) === 1) {
            $interpolated = $this->matchesInterpolatedExceptionMessage($tokens, $items[0], $var);
            if ($interpolated !== null) {
                return ['hasNewline' => $interpolated['hasNewline'], 'changesOutput' => true];
            }

            $replacement = $this->rewriteInterpolatedStringToEcho($code, $tokens, $items[0], $var);
            if ($replacement !== null) {
                return ['changesOutput' => true, 'replacement' => $replacement];
            }
        }

        if (count($items) === 1 || count($items) === 2) {
            if ($this->matchesMessage($tokens, $items[0], $var)) {
                if (count($items) === 1) {
                    return ['hasNewline' => false, 'changesOutput' => true];
                }
                if ($this->matchesNewline($tokens, $items[1])) {
                    return ['hasNewline' => true, 'changesOutput' => true];
                }
            }
        }

        if (count($items) === 3 || count($items) === 4) {
            if (
                $this->matchesClass($tokens, $items[0], $var)
                && $this->matchesColonSeparator($tokens, $items[1])
                && $this->matchesMessage($tokens, $items[2], $var)
            ) {
                $changesOutput = !$this->matchesString($tokens, $items[1], ': ');
                if (count($items) === 3) {
                    return ['hasNewline' => false, 'changesOutput' => $changesOutput];
                }
                if ($this->matchesNewline($tokens, $items[3])) {
                    return ['hasNewline' => true, 'changesOutput' => $changesOutput];
                }
            }
        }

        $classAware = $this->matchesClassAwareWrapper($tokens, $items, $var);
        if ($classAware !== null) {
            return $classAware;
        }

        if (count($items) === 2 || count($items) === 3) {
            if (
                $this->matchesClassPrefixString($tokens, $items[0])
                && $this->matchesMessage($tokens, $items[1], $var)
            ) {
                if (count($items) === 2) {
                    return ['hasNewline' => false, 'changesOutput' => true];
                }
                if ($this->matchesNewline($tokens, $items[2])) {
                    return ['hasNewline' => true, 'changesOutput' => true];
                }
            }
        }

        if (count($items) === 2 || count($items) === 3) {
            $leadingWhitespace = $this->leadingWhitespaceBeforeClassPrefix($tokens, $items[0]);
            if ($leadingWhitespace !== null && $this->matchesMessage($tokens, $items[1], $var)) {
                if (count($items) === 2) {
                    return [
                        'changesOutput' => true,
                        'replacement' => $this->canonicalEchoStatement($var, false, $leadingWhitespace),
                    ];
                }
                if ($this->matchesNewline($tokens, $items[2])) {
                    return [
                        'changesOutput' => true,
                        'replacement' => $this->canonicalEchoStatement($var, true, $leadingWhitespace),
                    ];
                }
            }
        }

        if (!$this->containsClassSource($tokens, $echoIndex, $statementEnd)) {
            $messageRange = $this->findDirectMessageItem($tokens, $items, $var);
            if ($messageRange !== null) {
                return ['changesOutput' => true, 'messageRange' => $messageRange];
            }
        }

        return null;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param list<array{start: int, end: int}> $items
     * @return array{hasNewline: bool, changesOutput: bool}|null
     */
    private function matchesClassAwareWrapper(array $tokens, array $items, string $var): ?array
    {
        if (
            (count($items) === 2 || count($items) === 3)
            && $this->matchesClass($tokens, $items[0], $var)
            && $this->matchesMessage($tokens, $items[1], $var)
        ) {
            if (count($items) === 2) {
                return ['hasNewline' => false, 'changesOutput' => true];
            }
            if ($this->matchesNewline($tokens, $items[2])) {
                return ['hasNewline' => true, 'changesOutput' => true];
            }
        }

        if (
            (count($items) === 4 || count($items) === 5)
            && $this->matchesClass($tokens, $items[0], $var)
            && $this->matchesString($tokens, $items[1], ": '")
            && $this->matchesMessage($tokens, $items[2], $var)
        ) {
            $hasNewline = $this->matchesSuffixString($tokens, $items[3], "'", true);
            if ($hasNewline !== null && count($items) === 4) {
                return ['hasNewline' => $hasNewline, 'changesOutput' => true];
            }
            if (count($items) === 5 && $this->matchesString($tokens, $items[3], "'") && $this->matchesNewline($tokens, $items[4])) {
                return ['hasNewline' => true, 'changesOutput' => true];
            }
        }

        if (count($items) === 4 || count($items) === 5) {
            $hasNewline = $this->matchesWrappedClassMessage($tokens, $items, $var, 'Exception (', '): ');
            if ($hasNewline !== null) {
                return ['hasNewline' => $hasNewline, 'changesOutput' => true];
            }

            $hasNewline = $this->matchesWrappedClassMessage($tokens, $items, $var, '[', '] ');
            if ($hasNewline !== null) {
                return ['hasNewline' => $hasNewline, 'changesOutput' => true];
            }

            $hasNewline = $this->matchesCaughtClassMessage($tokens, $items, $var);
            if ($hasNewline !== null) {
                return ['hasNewline' => $hasNewline, 'changesOutput' => true];
            }
        }

        return null;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param list<array{start: int, end: int}> $items
     */
    private function matchesWrappedClassMessage(array $tokens, array $items, string $var, string $prefix, string $separator): ?bool
    {
        if (
            !$this->matchesString($tokens, $items[0], $prefix)
            || !$this->matchesClass($tokens, $items[1], $var)
            || !$this->matchesString($tokens, $items[2], $separator)
            || !$this->matchesMessage($tokens, $items[3], $var)
        ) {
            return null;
        }

        if (count($items) === 4) {
            return false;
        }

        return $this->matchesNewline($tokens, $items[4]) ? true : null;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param list<array{start: int, end: int}> $items
     */
    private function matchesCaughtClassMessage(array $tokens, array $items, string $var): ?bool
    {
        if (
            count($items) !== 5
            || !$this->matchesString($tokens, $items[0], 'Caught ')
            || !$this->matchesClass($tokens, $items[1], $var)
            || !$this->matchesString($tokens, $items[2], '(')
            || !$this->matchesMessage($tokens, $items[3], $var)
        ) {
            return null;
        }

        return $this->matchesSuffixString($tokens, $items[4], ')', true);
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     */
    private function isSupportedOutputStatementStart(array $tokens, int $index): bool
    {
        $id = $tokens[$index]['id'] ?? null;
        if (in_array($id, [T_ECHO, T_PRINT], true)) {
            return true;
        }

        return $id === T_STRING && strtolower($tokens[$index]['text']) === 'var_dump';
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @return list<array{start: int, end: int}>|null
     */
    private function parseEchoItems(array $tokens, int $start, int $end): ?array
    {
        $items = [];
        $itemStart = $this->nextSignificant($tokens, $start);
        if ($itemStart === null || $itemStart >= $end) {
            return null;
        }

        $itemEnd = $this->previousSignificant($tokens, $end - 1);
        if (
            $itemEnd !== null
            && $tokens[$itemStart]['text'] === '('
            && $tokens[$itemEnd]['text'] === ')'
            && $this->findMatching($tokens, $itemStart) === $itemEnd
        ) {
            return $this->parseEchoItems($tokens, $itemStart + 1, $itemEnd);
        }

        $paren = 0;
        $bracket = 0;
        $brace = 0;
        for ($i = $itemStart; $i < $end; $i++) {
            $id = $tokens[$i]['id'] ?? null;
            if ($id !== null && $id !== T_CURLY_OPEN) {
                continue;
            }

            $text = $tokens[$i]['text'];
            if ($text === '(') {
                $paren++;
                continue;
            }
            if ($text === ')') {
                $paren--;
                continue;
            }
            if ($text === '[') {
                $bracket++;
                continue;
            }
            if ($text === ']') {
                $bracket--;
                continue;
            }
            if ($text === '{') {
                $brace++;
                continue;
            }
            if ($text === '}') {
                $brace--;
                continue;
            }
            if (($text === ',' || $text === '.') && $paren === 0 && $bracket === 0 && $brace === 0) {
                $item = $this->parseEchoItem($tokens, $itemStart, $i);
                if ($item === null) {
                    return null;
                }
                $items[] = $item;
                $next = $this->nextSignificant($tokens, $i + 1);
                if ($next === null || $next >= $end) {
                    return null;
                }
                $itemStart = $next;
            }
        }

        $item = $this->parseEchoItem($tokens, $itemStart, $end);
        if ($item === null) {
            return null;
        }
        $items[] = $item;

        return $items;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @return array{start: int, end: int}|null
     */
    private function parseEchoItem(array $tokens, int $start, int $end): ?array
    {
        $start = $this->nextSignificant($tokens, $start);
        $end = $this->previousSignificant($tokens, $end - 1);
        if ($start === null || $end === null || $start > $end) {
            return null;
        }

        while (
            $tokens[$start]['text'] === '('
            && $tokens[$end]['text'] === ')'
            && $this->findMatching($tokens, $start) === $end
        ) {
            $start = $this->nextSignificant($tokens, $start + 1);
            $end = $this->previousSignificant($tokens, $end - 1);
            if ($start === null || $end === null || $start > $end) {
                return null;
            }
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param array{start: int, end: int} $range
     */
    private function matchesMessage(array $tokens, array $range, string $var): bool
    {
        $i = $range['start'];
        if (($tokens[$i]['id'] ?? null) !== T_VARIABLE || $tokens[$i]['text'] !== $var) {
            return false;
        }

        $i = $this->nextSignificant($tokens, $i + 1);
        if ($i === null || ($tokens[$i]['id'] ?? null) !== T_OBJECT_OPERATOR) {
            return false;
        }

        $i = $this->nextSignificant($tokens, $i + 1);
        if ($i === null || strtolower($tokens[$i]['text']) !== 'getmessage') {
            return false;
        }

        $i = $this->nextSignificant($tokens, $i + 1);
        if ($i === null || $tokens[$i]['text'] !== '(') {
            return false;
        }

        $closeParen = $this->findMatching($tokens, $i);
        if ($closeParen === null || $closeParen !== $range['end']) {
            return false;
        }

        return $this->nextSignificant($tokens, $i + 1) === $closeParen;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param array{start: int, end: int} $range
     */
    private function matchesClass(array $tokens, array $range, string $var): bool
    {
        return $this->matchesGetClass($tokens, $range, $var)
            || $this->matchesClassConstant($tokens, $range, $var);
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param array{start: int, end: int} $range
     */
    private function matchesGetClass(array $tokens, array $range, string $var): bool
    {
        $i = $range['start'];
        if ($tokens[$i]['text'] === '\\') {
            $i = $this->nextSignificant($tokens, $i + 1);
            if ($i === null) {
                return false;
            }
        }

        if (strtolower($tokens[$i]['text']) !== 'get_class') {
            return false;
        }

        $i = $this->nextSignificant($tokens, $i + 1);
        if ($i === null || $tokens[$i]['text'] !== '(') {
            return false;
        }

        $arg = $this->nextSignificant($tokens, $i + 1);
        if ($arg === null || ($tokens[$arg]['id'] ?? null) !== T_VARIABLE || $tokens[$arg]['text'] !== $var) {
            return false;
        }

        $closeParen = $this->nextSignificant($tokens, $arg + 1);
        if ($closeParen === null || $tokens[$closeParen]['text'] !== ')' || $closeParen !== $range['end']) {
            return false;
        }

        return true;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param array{start: int, end: int} $range
     */
    private function matchesClassConstant(array $tokens, array $range, string $var): bool
    {
        $i = $range['start'];
        if (($tokens[$i]['id'] ?? null) !== T_VARIABLE || $tokens[$i]['text'] !== $var) {
            return false;
        }

        $i = $this->nextSignificant($tokens, $i + 1);
        if ($i === null || ($tokens[$i]['id'] ?? null) !== T_DOUBLE_COLON) {
            return false;
        }

        $i = $this->nextSignificant($tokens, $i + 1);
        return $i !== null && strtolower($tokens[$i]['text']) === 'class' && $i === $range['end'];
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param array{start: int, end: int} $range
     */
    private function matchesString(array $tokens, array $range, string $expected): bool
    {
        if ($range['start'] !== $range['end'] || ($tokens[$range['start']]['id'] ?? null) !== T_CONSTANT_ENCAPSED_STRING) {
            return false;
        }

        return $this->decodeString($tokens[$range['start']]['text']) === $expected;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param array{start: int, end: int} $range
     */
    private function matchesColonSeparator(array $tokens, array $range): bool
    {
        $decoded = $this->decodeConstantString($tokens, $range);
        return $decoded !== null && trim($decoded) === ':';
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param array{start: int, end: int} $range
     */
    private function matchesSuffixString(array $tokens, array $range, string $suffix, bool $allowNewline): ?bool
    {
        $decoded = $this->decodeConstantString($tokens, $range);
        if ($decoded === null) {
            return null;
        }

        if ($decoded === $suffix) {
            return false;
        }

        if ($allowNewline && $decoded === $suffix . "\n") {
            return true;
        }

        return null;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param array{start: int, end: int} $range
     */
    private function decodeConstantString(array $tokens, array $range): ?string
    {
        if ($range['start'] !== $range['end'] || ($tokens[$range['start']]['id'] ?? null) !== T_CONSTANT_ENCAPSED_STRING) {
            return null;
        }

        return $this->decodeString($tokens[$range['start']]['text']);
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param array{start: int, end: int} $range
     */
    private function matchesClassPrefixString(array $tokens, array $range): bool
    {
        if ($range['start'] !== $range['end'] || ($tokens[$range['start']]['id'] ?? null) !== T_CONSTANT_ENCAPSED_STRING) {
            return false;
        }

        $decoded = $this->decodeString($tokens[$range['start']]['text']);
        return $decoded !== null && $this->isClassPrefix($decoded);
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param array{start: int, end: int} $range
     */
    private function leadingWhitespaceBeforeClassPrefix(array $tokens, array $range): ?string
    {
        $decoded = $this->decodeConstantString($tokens, $range);
        if ($decoded === null) {
            return null;
        }

        if (preg_match('/^(\s+)([A-Za-z_\\\\][A-Za-z0-9_\\\\]*: )$/', $decoded, $matches)) {
            return $this->isClassPrefix($matches[2]) ? $matches[1] : null;
        }

        if (preg_match('/^(\s*)(exception|error) $/i', $decoded, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function canonicalEchoStatement(string $var, bool $hasNewline, string $prefix = ''): string
    {
        $parts = ['echo'];
        if ($prefix !== '') {
            $parts[] = ' ' . $this->quoteString($prefix) . ',';
        }
        $parts[] = ' ' . $var . '::class, ": ", ' . $var . '->getMessage()';
        if ($hasNewline) {
            $parts[] = ', "\n"';
        }
        $parts[] = ';';

        return implode('', $parts);
    }

    private function quoteString(string $text): string
    {
        return '"' . addcslashes($text, "\\\"\n\r\t\v\f") . '"';
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param array{start: int, end: int} $range
     */
    private function matchesNewline(array $tokens, array $range): bool
    {
        if ($this->matchesString($tokens, $range, "\n")) {
            return true;
        }

        $text = '';
        for ($i = $range['start']; $i <= $range['end']; $i++) {
            if ($this->isIgnorable($tokens[$i])) {
                continue;
            }
            $text .= $tokens[$i]['text'];
        }

        return strtolower(ltrim($text, '\\')) === 'php_eol';
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param array{start: int, end: int} $range
     * @return array{hasNewline: bool}|null
     */
    private function matchesInterpolatedExceptionMessage(array $tokens, array $range, string $var): ?array
    {
        $i = $range['start'];
        if ($tokens[$i]['text'] !== '"') {
            return null;
        }

        $i = $this->nextSignificant($tokens, $i + 1);
        if (
            $i === null
            || ($tokens[$i]['id'] ?? null) !== T_ENCAPSED_AND_WHITESPACE
            || !$this->isClassPrefix($tokens[$i]['text'])
        ) {
            return null;
        }

        $i = $this->nextSignificant($tokens, $i + 1);
        if ($i === null || ($tokens[$i]['id'] ?? null) !== T_CURLY_OPEN) {
            return null;
        }

        $i = $this->nextSignificant($tokens, $i + 1);
        if ($i === null || ($tokens[$i]['id'] ?? null) !== T_VARIABLE || $tokens[$i]['text'] !== $var) {
            return null;
        }

        $i = $this->nextSignificant($tokens, $i + 1);
        if ($i === null || ($tokens[$i]['id'] ?? null) !== T_OBJECT_OPERATOR) {
            return null;
        }

        $i = $this->nextSignificant($tokens, $i + 1);
        if ($i === null || strtolower($tokens[$i]['text']) !== 'getmessage') {
            return null;
        }

        $i = $this->nextSignificant($tokens, $i + 1);
        if ($i === null || $tokens[$i]['text'] !== '(') {
            return null;
        }

        $closeParen = $this->findMatching($tokens, $i);
        if ($closeParen === null) {
            return null;
        }

        $i = $this->nextSignificant($tokens, $closeParen + 1);
        if ($i === null || $tokens[$i]['text'] !== '}') {
            return null;
        }

        $i = $this->nextSignificant($tokens, $i + 1);
        $hasNewline = false;
        if (($tokens[$i]['id'] ?? null) === T_ENCAPSED_AND_WHITESPACE) {
            if ($tokens[$i]['text'] !== '\n') {
                return null;
            }
            $hasNewline = true;
            $i = $this->nextSignificant($tokens, $i + 1);
        }

        if ($i === null || $tokens[$i]['text'] !== '"' || $i !== $range['end']) {
            return null;
        }

        return ['hasNewline' => $hasNewline];
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param array{start: int, end: int} $range
     */
    private function rewriteInterpolatedStringToEcho(string $code, array $tokens, array $range, string $var): ?string
    {
        if ($tokens[$range['start']]['text'] !== '"' || $tokens[$range['end']]['text'] !== '"') {
            return null;
        }

        $parts = [];
        $hasMessage = false;

        for ($i = $range['start'] + 1; $i < $range['end']; $i++) {
            $id = $tokens[$i]['id'] ?? null;
            if ($id === T_ENCAPSED_AND_WHITESPACE) {
                if ($tokens[$i]['text'] !== '') {
                    $parts[] = $this->quoteInterpolatedTextSegment($tokens[$i]['text']);
                }
                continue;
            }

            if ($id === T_VARIABLE) {
                $parts[] = $tokens[$i]['text'];
                continue;
            }

            if ($id !== T_CURLY_OPEN) {
                return null;
            }

            $close = $this->findMatching($tokens, $i);
            if ($close === null || $close > $range['end']) {
                return null;
            }

            $exprStart = $this->nextSignificant($tokens, $i + 1);
            $exprEnd = $this->previousSignificant($tokens, $close - 1);
            if ($exprStart === null || $exprEnd === null || $exprStart > $exprEnd) {
                return null;
            }

            $exprRange = ['start' => $exprStart, 'end' => $exprEnd];
            if ($this->matchesMessage($tokens, $exprRange, $var)) {
                $parts[] = $var . '::class';
                $parts[] = '": "';
                $parts[] = $var . '->getMessage()';
                $hasMessage = true;
            } else {
                $exprStartOffset = $tokens[$exprStart]['offset'];
                $exprEndOffset = $tokens[$exprEnd]['offset'] + strlen($tokens[$exprEnd]['text']);
                $parts[] = substr($code, $exprStartOffset, $exprEndOffset - $exprStartOffset);
            }

            $i = $close;
        }

        if (!$hasMessage) {
            return null;
        }

        return 'echo ' . implode(', ', $parts) . ';';
    }

    private function quoteInterpolatedTextSegment(string $text): string
    {
        return '"' . $text . '"';
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     * @param list<array{start: int, end: int}> $items
     * @return array{start: int, end: int}|null
     */
    private function findDirectMessageItem(array $tokens, array $items, string $var): ?array
    {
        foreach ($items as $item) {
            if ($this->matchesMessage($tokens, $item, $var)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     */
    private function containsClassSource(array $tokens, int $start, int $end): bool
    {
        for ($i = $start; $i <= $end; $i++) {
            if (($tokens[$i]['id'] ?? null) === T_DOUBLE_COLON) {
                $next = $this->nextSignificant($tokens, $i + 1);
                if ($next !== null && strtolower($tokens[$next]['text']) === 'class') {
                    return true;
                }
            }

            if (strtolower($tokens[$i]['text']) === 'get_class') {
                return true;
            }
        }

        return false;
    }

    private function classPrefixLengthAt(string $line, int $offset): ?int
    {
        if (!preg_match('/\A[A-Za-z_\\\\][A-Za-z0-9_\\\\]*: /', substr($line, $offset), $match)) {
            return null;
        }

        return $this->isClassPrefix($match[0]) ? strlen($match[0]) : null;
    }

    private function isClassPrefix(string $text): bool
    {
        if (!str_ends_with($text, ': ')) {
            return false;
        }

        $class = substr($text, 0, -2);
        if (preg_match('/^[A-Za-z_\\\\][A-Za-z0-9_\\\\]*$/', $class) !== 1) {
            return false;
        }

        $lowerClass = strtolower($class);
        return $lowerClass === 'exception'
            || $lowerClass === 'error'
            || str_ends_with($lowerClass, 'exception')
            || str_ends_with($lowerClass, 'error');
    }

    private function decodeString(string $literal): ?string
    {
        if (strlen($literal) < 2) {
            return null;
        }

        $quote = $literal[0];
        if ($quote !== "'" && $quote !== '"') {
            return null;
        }

        $inner = substr($literal, 1, -1);
        if ($quote === "'") {
            return str_replace(['\\\\', "\\'"], ['\\', "'"], $inner);
        }

        return stripcslashes($inner);
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     */
    private function findMatching(array $tokens, int $openIndex): ?int
    {
        $open = $tokens[$openIndex]['text'];
        $close = match ($open) {
            '(' => ')',
            '[' => ']',
            '{' => '}',
            default => null,
        };
        if ($close === null) {
            return null;
        }

        $depth = 0;
        $count = count($tokens);
        for ($i = $openIndex; $i < $count; $i++) {
            if ($tokens[$i]['text'] === $open) {
                $depth++;
            } elseif ($tokens[$i]['text'] === $close) {
                $depth--;
                if ($depth === 0) {
                    return $i;
                }
            }
        }

        return null;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     */
    private function findNextText(array $tokens, string $text, int $start, ?int $end = null): ?int
    {
        $end ??= count($tokens);
        for ($i = $start; $i < $end; $i++) {
            if ($tokens[$i]['text'] === $text) {
                return $i;
            }
        }

        return null;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     */
    private function nextSignificant(array $tokens, int $start): ?int
    {
        $count = count($tokens);
        for ($i = $start; $i < $count; $i++) {
            if (!$this->isIgnorable($tokens[$i])) {
                return $i;
            }
        }

        return null;
    }

    /**
     * @param list<array{id: int|null, text: string, offset: int}> $tokens
     */
    private function previousSignificant(array $tokens, int $start): ?int
    {
        for ($i = $start; $i >= 0; $i--) {
            if (!$this->isIgnorable($tokens[$i])) {
                return $i;
            }
        }

        return null;
    }

    /**
     * @param array{id: int|null, text: string, offset: int} $token
     */
    private function isIgnorable(array $token): bool
    {
        return in_array($token['id'], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true);
    }
}
