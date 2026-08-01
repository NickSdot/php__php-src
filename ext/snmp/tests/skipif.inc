<?php
require_once (dirname(__FILE__).'/snmp_include.inc');
require_once dirname(__DIR__, 3) . '/tests/run-test/test_probe_cache.inc';

function get_snmp_test_agent_unavailable_reason(): ?string
{
    global $hostname, $community, $timeout;

    $probe = static fn(): ?string =>
        @snmpget($hostname, $community, '.1.3.6.1.2.1.1.1.0', $timeout) === false
            ? 'NO SNMPD on this host or community invalid'
            : null;

    $configuration = [$hostname, $community, $timeout];
    return run_test_cache_probe_failure('snmp', $configuration, $probe);
}

//test server is available
// this require snmpget to work ...
//snmpget  ( string $hostname  , string $community  ,
//string $object_id  [, int $timeout  [, int $retries  ]] )

$reason = get_snmp_test_agent_unavailable_reason();
if (is_string($reason)) {
    die("skip $reason");
}
