--TEST--
DatePeriod: Test wrong recurrence parameter on __construct
--FILE--
<?php
try {
    new DatePeriod(new DateTime('yesterday'), new DateInterval('P1D'), 0);
} catch (Exception $exception) {
    echo $exception::class, ': ', $exception->getMessage(), PHP_EOL;
}

try {
    new DatePeriod(new DateTime('yesterday'), new DateInterval('P1D'), -1);
} catch (Exception $exception) {
    echo $exception::class, ': ', $exception->getMessage(), PHP_EOL;
}

?>
--EXPECTF--
DateMalformedPeriodStringException: DatePeriod::__construct(): Recurrence count must be greater or equal to 1 and lower than %d
DateMalformedPeriodStringException: DatePeriod::__construct(): Recurrence count must be greater or equal to 1 and lower than %d
