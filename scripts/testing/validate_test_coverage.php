#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/autoload.php';

use PHP\Testing\TestCoverageCommand;

exit((new TestCoverageCommand())->run($argv));
