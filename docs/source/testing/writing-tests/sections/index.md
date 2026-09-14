# PHPT Test File Sections

PHPT tests consist of different named sections, each serving a different purpose. For instance, for
metadata, setup, input, failure handling, and expected output assertions. Most sections are, while
generally useful, optional; this reference explains when and how each one is used.

## Types

**Metadata:**
 [TEST](#--test--),
 [DESCRIPTION](#--description--),
 [CREDITS](#--credits--)

**Runtime Setup:**
 [INI](#--ini--),
 [EXTENSIONS](#--extensions--)

**Test Control:**
 [SKIPIF](#--skipif--),
 [XFAIL](#--xfail--),
 [XLEAK](#--xleak--),
 [FLAKY](#--flaky--),
 [CONFLICTS](#--conflicts--),
 [WHITESPACE_SENSITIVE](#--whitespace_sensitive--)

**Invocation:**
 [ENV](#--env--),
 [ARGS](#--args--),
 [CGI](#--cgi--),
 [PHPDBG](#--phpdbg--),
 [CAPTURE_STDIO](#--capture_stdio--)

**Input:**
 [GET](#--get--),
 [POST](#--post--),
 [POST_RAW](#--post_raw--),
 [PUT](#--put--),
 [GZIP_POST](#--gzip_post--),
 [DEFLATE_POST](#--deflate_post--),
 [COOKIE](#--cookie--),
 [STDIN](#--stdin--)

**Test Source:**
 [FILE](#--file--),
 [FILEEOF](#--fileeof--),
 [FILE_EXTERNAL](#--file_external--),
 [REDIRECTTEST](#--redirecttest--)

**Cleanup:**
 [CLEAN](#--clean--)

**Expected Headers:**
 [EXPECTHEADERS](#--expectheaders--)

**Expected Output:**
 [EXPECT](#--expect--),
 [EXPECTF](#--expectf--),
 [EXPECTREGEX](#--expectregex--),
 [EXPECT_EXTERNAL](#--expect_external--),
 [EXPECTF_EXTERNAL](#--expectf_external--),
 [EXPECTREGEX_EXTERNAL](#--expectregex_external--)

## Reference

### --TEST--

Every test needs a concise, single-line title. Aside from giving ourselves context in the test file,
the test runner uses the content of the TEST section to identify the test in its output.

|               |               |
|---------------|---------------|
| **Mandatory** | Yes           |
| **Position**  | First in file |
| **Content**   | Plain text    |
| **Lines**     | One           |

A good title describes the behaviour and case well without relying on the filename. Prefer titles
that make clear which underlying API and behaviour is tested. When multiple tests form a group,
it's a common pattern to reflect this in the title by sorting information from broad to more
specific, e.g. API, outcome, then input. Below are examples that are meant to give inspiration, but
there are no hard rules.

#### When **Not** to Use

- for longer explanations or more context, use [DESCRIPTION](#--description--).

#### Examples

Assume `host_success_ipv6.phpt` verifies that `setHost()` can set an IPv6 address:

```text
--TEST--
Test Uri\Rfc3986\UriBuilder::setHost() - success - IPv6 address
```

The title is concise and clearly communicates what is tested, the expected outcome, and the input.
The inverse would be `host_error_ipv6_closing_brace.phpt`:

```text
--TEST--
Test Uri\Rfc3986\UriBuilder::setHost() - error - missing IPv6 closing brace
```

The titles alone, not the code, make us understand what these tests do. Structured titles like these
also help to locate related tests even if they do not live next to each other:

```text
Test Uri\Rfc3986\Uri component modification - userinfo - empty string
Test Uri\Rfc3986\Uri component modification - userinfo - reserved characters
Test Uri\Rfc3986\Uri component modification - userinfo - unsetting existing

Test Uri\Rfc3986\Uri component modification - query - empty string
Test Uri\Rfc3986\Uri component modification - query - URL encoded characters
Test Uri\Rfc3986\Uri component modification - query - unsetting existing
```

For a regression test, begin with the issue number followed by a description of what behaviour is
tested rather than just repeating the issue title:

```text
--TEST--
GH-19892: Successful \Uri\WhatWg\Url::parse() with empty errors array
```

### --DESCRIPTION--

Descriptions should rarely be needed. If your test really needs more than a title and its code to
adequately describe it, DESCRIPTION can be used for further explanation.

|               |                  |
|---------------|------------------|
| **Mandatory** | No               |
| **Position**  | After `--TEST--` |
| **Content**   | Plain text       |
| **Lines**     | One or more      |

Ideally, a test is self-explanatory. In most cases the test file name and its code - perhaps with
some inline comments - should be enough to understand a test. If that is not the case, it could mean
that the test does more than it should. The test runner does not use the description in any output.

### --CREDITS--

Sometimes it is required to credit a contributor, for example, when someone privately reported a
security issue or otherwise collaborated externally. Use CREDITS only for such cases.

|               |                                       |
|---------------|---------------------------------------|
| **Mandatory** | No                                    |
| **Position**  | After `--TEST--` or `--DESCRIPTION--` |
| **Content**   | Contributor credits                   |
| **Lines**     | One or more                           |
| **Separator** | Newline                               |

> [!WARNING]
> For newly created tests, do not use this section for simple authorship claims or to list everyone
> who edited the test; Git already records that. It may provide more specific attribution, such as
> crediting the original reporter or a contributor not named in a `Co-authored-by` tag.

#### Examples

Names, usernames, profile links, e-mail addresses, and combinations of these are allowed. When more
than one contributor is credited, they must be separated with a newline.

```text
--CREDITS--
Felipe Pena

--CREDITS--
https://github.com/recepasan

--CREDITS--
Antonio Diaz Ruiz <dejalatele@gmail.com>
```

If the test was part of a TestFest or another event, put the contributor name and email on the first
line, and # followed by the name of the event and the date (YYYY-MM-DD) on the second line.

```text
--CREDITS--
Zoe Slattery zoe@php.net
# TestFest Munich 2009-05-19

--CREDITS--
marcosptf - <marcosptf@yahoo.com.br>
#phparty7 - @phpsp - novatec/2015 - sao paulo - br
```

### --INI--

Many tests require customised `php.ini` settings. INI is the inline version of a `php.ini` file, and
allows custom settings for the test that are set before every test invocation.

|               |                                       |
|---------------|---------------------------------------|
| **Mandatory** | No                                    |
| **Position**  | Before execution section              |
| **Content**   | PHP INI settings, e.g. `precision=14` |
| **Lines**     | One or more                           |
| **Separator** | Newline                               |

`php.ini` rules apply: each line is one key-value pair. Lines beginning with `;` or `#` are
comments. Values may include placeholders which will be automatically replaced before the test runs.

#### Placeholders

| Placeholder | Description                 | Example                         |
|------------|-----------------------------|---------------------------------|
| `{PWD}`    | Current test file directory | `opcache.preload={PWD}/foo.inc` |
| `{TMP}`    | System temporary directory  | `opcache.file_cache="{TMP}"`    |

> [!WARNING]
> The section is not validated; invalid settings may cause unexpected behaviour or failures.

#### Examples

```text
--INI--
precision=14
opcache.enable=1
opcache.file_cache="{TMP}"
opcache.preload={PWD}/foo.inc
```

### --ENV--

Configures environment variables such as those found in the `$_SERVER` global
array.

|               |                                          |
|---------------|------------------------------------------|
| **Mandatory** | No                                       |
| **Position**  | Before execution section                 |
| **Content**   | Environment variables, e.g. `NAME=value` |
| **Case**      | Platform-dependent                       |
| **Lines**     | One or more                              |
| **Separator** | Newline                                  |

#### Placeholders

| Placeholder | Description                 | Example                      |
|-------------|-----------------------------|------------------------------|
| `{PWD}`     | Current test file directory | `LDAPCONF={PWD}/ldap.conf`   |

#### Examples

**Example 1**

```text
--ENV--
SCRIPT_NAME=/frontcontroller10.php
REQUEST_URI=/frontcontroller10.php/hi
PATH_INFO=/hi
```

Full example: [sample018.phpt](../samples/index.md#sample018phpt)

### --EXTENSIONS--

Use EXTENSIONS when a test needs additional PHP extensions.

|               |                              |
|---------------|------------------------------|
| **Mandatory** | No                           |
| **Position**  | Before execution section     |
| **Content**   | Extension names, e.g. `curl` |
| **Case**      | Sensitive                    |
| **Lines**     | One or more                  |
| **Separator** | Newline                      |

The runner loads listed extensions when necessary. If an extension is unavailable as a built-in or
shared module, it skips the test.

#### Examples

Assume a test in `ext/uri` needs reflection to exercise the behaviour under test. List the
`reflection` extension so the runner loads it:

```php
--EXTENSIONS--
reflection
--FILE--
<?php
try {
    $reflectionClass = new ReflectionClass(Uri\Rfc3986\Uri::class);
    $reflectionClass->newInstanceWithoutConstructor();
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}
?>
```

The `zend_test` extension exposes helpers used only by tests. List it when a test calls one of them:

```php
--EXTENSIONS--
zend_test
--FILE--
<?php
$url = 'https://%65user:%65pass@%65xample.com:123';
var_dump(zend_test_uri_parser($url, 'parse_url'));
?>
```

### --SKIPIF--

Some tests should not run under specific conditions. Use SKIPIF to skip them based on the build,
operating system, dependencies, environment, or other conditions. It can also apply XFAIL, XLEAK,
or FLAKY only when a condition is met.

|               |                               |
|---------------|-------------------------------|
| **Mandatory** | No                            |
| **Position**  | Before execution section      |
| **Content**   | PHP code enclosed by PHP tags |

#### Output Conventions

When a condition matches, stop the check with `die('prefix reason')`. If none of the conditions
produces output, the test runs normally.

| Output (`prefix <reason>`) | Test                    | Result                          |
|----------------------------|-------------------------|---------------------------------|
| *(none)*                   | Runs                    | Normal result                   |
| `skip <reason>`            | Skips                   | Reports SKIP                    |
| `info <reason>`            | Runs                    | Adds information                |
| `warn <reason>`            | Runs                    | Adds warning                    |
| `xfail <reason>`           | Runs                    | Conditional [XFAIL](#--xfail--) |
| `xleak <reason>`           | Runs, without ASAN/MSAN | Conditional [XLEAK](#--xleak--) |
| `flaky <reason>`           | Runs                    | Conditional [FLAKY](#--flaky--) |

Any non-empty output that does not start with one of the six accepted prefixes is invalid and causes
the test to BORK.

#### When **Not** to Use

- If a test fails normally, fix the cause rather than skipping the test.
- If a test is flaky in every supported configuration, use [FLAKY](#--flaky--).
- If a persistent failure affects every supported configuration, use [XFAIL](#--xfail--).
- If a known leak appears in every supported Valgrind run, use [XLEAK](#--xleak--).

#### Examples

Assume a sodium test needs Argon2i. Loading the extension is not enough because the version of
libsodium may not provide the feature. Hence, list extension, then check for the feature in SKIPIF:

```php
--TEST--
Test sodium password hashing with Argon2i
--EXTENSIONS--
sodium
--SKIPIF--
<?php
if (!defined('SODIUM_CRYPTO_PWHASH_SALTBYTES')) {
    die('skip libsodium without Argon2i');
}
?>
```

If the constant is missing, the runner skips the test and reports the reason. Otherwise, the check
produces no output and the test runs. Remember to [load the extension explicitly](#--extensions--); without
EXTENSIONS, an available shared extension may not be loaded when this check runs.

The same pattern applies to platform-specific behaviour. This zlib test covers mandatory file
locking on Windows, so it should not run elsewhere:

```php
--TEST--
Test mandatory locking on Windows
--EXTENSIONS--
zlib
--SKIPIF--
<?php
if (PHP_OS_FAMILY !== 'Windows') {
    die('skip Depends on Windows mandatory file locking');
}
?>
```

The test is skipped on other systems with the reason that identifies the behaviour, not merely the
required platform.

Sometimes the test should run everywhere, but a persistent failure affects only one config. Return
`xfail <reason>` from SKIPIF so the remaining configs must still pass:

```php
--TEST--
Output buffering rejects recursive buffer flushing
--SKIPIF--
<?php
$jitEnabled = ini_get('opcache.jit')
  && ini_get('opcache.jit_buffer_size');

if (PHP_OS_FAMILY === 'Windows' && $jitEnabled && !PHP_DEBUG) {
    die('xfail JIT-enabled release builds crash on Windows');
}
?>
```

#### Shared Checks

Often, several tests need the exact same checks. A common pattern is to place them in `skipif.inc`
beside the tests and include that file from each test's SKIPIF section using `__DIR__`.

```php
--SKIPIF--
<?php
require __DIR__ . '/skipif.inc';
?>
```

The content of a `skipif.inc` is identical to content in SKIPIF sections. The conventions of SKIPIF
must be followed, and the script must not contain any test code or other outputs:

```php
<?php
if (PHP_OS_FAMILY !== 'Windows') {
    die('skip Depends on Windows mandatory file locking');
}

// root not allowed when not set in env
if (!getmyuid() && !getenv('TEST_FPM_RUN_AS_ROOT')) {
    die('skip Cannot run as root');
}
```

> [!NOTE]
> Shared helpers should use `.inc`. The runner owns `.php`, `.log`, `.mem`, `.exp`, `.out` and
> `.diff` files next to PHPT tests and may overwrite or remove them.

### --XFAIL--

Some known test failures cannot be fixed straight away. Use XFAIL when such a failure affects all
supported configs. The test still runs, and the failure is treated as expected.

|                  |                                     |
|------------------|-------------------------------------|
| **Mandatory**    | No                                  |
| **Position**     | Before execution section            |
| **Content**      | Plain text                          |
| **Lines**        | One or more                         |
| **Alternatives** | [§](index.md#--xleak--) `--XLEAK--` |
|                  | [§](index.md#--flaky--) `--FLAKY--` |

The test runner still executes the test and compares its output. A mismatch is
reported as XFAIL instead of FAIL. If the test unexpectedly passes, the runner
warns. This likely means that the underlying issue has been fixed and XFAIL may
no longer be needed.

Write a short reason that explains what remains broken. Include an issue
reference when it helps track the work but keep the explanation useful on its
own.

#### When **Not** to Use

- If the test sometimes passes and sometimes fails, use [FLAKY](#--flaky--).
- If a prerequisite is missing, use [SKIPIF](#--skipif--) to skip the test.
- If the failure affects only one config, output `xfail <reason>` from [SKIPIF](#--skipif--).
- If the problem is a Valgrind leak, use [XLEAK](#--xleak--).

#### Examples

GH-18985 currently produces the wrong line number in optimiser output:

```text
--TEST--
GH-18985: Correct line numbers for multiline expressions
--EXTENSIONS--
opcache
--INI--
opcache.opt_debug_level=0x40010000
--XFAIL--
GH-18985: Optimiser uses wrong line for multiline expressions.
```

Nested iterators provide a different kind of persistent failure. The reason
states both the operation and the observable result:

```text
--TEST--
GH-10519: Nested RecursiveArrayIterator updates its backing array
--XFAIL--
GH-10519: Updating a nested array does not update the original array.
```

### --XLEAK--

Some known leaks cannot be fixed straight away. Use XLEAK when a leak is
expected whenever the test runs under Valgrind. It marks the leak as expected
without hiding other test failures.

|                  |                                     |
|------------------|-------------------------------------|
| **Mandatory**    | No                                  |
| **Position**     | Before execution section            |
| **Content**      | Plain text                          |
| **Lines**        | One or more                         |
| **Requires**     | Valgrind                            |
| **Alternatives** | [§](index.md#--xfail--) `--XFAIL--` |
|                  | [§](index.md#--flaky--) `--FLAKY--` |

With Valgrind, the runner reports a detected leak as XLEAK rather than LEAK and
shows the reason from this section. If the leak disappears, the runner warns.
This likely means that the underlying issue has been fixed and XLEAK may no
longer be needed.

XLEAK tests are skipped under ASAN and MSAN. Without Valgrind, XLEAK has no
effect on ordinary test failures. In the reason, identify the component and the
operation that leaks, and include an issue reference when useful.

#### When **Not** to Use

- If the expected leak affects only one config, output `xleak <reason>` from [SKIPIF](#--skipif--).
- If the expected problem is not a leak, use [XFAIL](#--xfail--) or [FLAKY](#--flaky--).

#### Examples

An exception thrown from an interrupt handler currently leaks during live-variable cleanup:

```text
--TEST--
Interrupt-handler exceptions during live-variable cleanup
--EXTENSIONS--
pcntl
posix
--XLEAK--
Interrupt-handler exceptions leak during live-variable cleanup.
```

Leaks may also come from a third-party library. Name that library, and if one exists, mention the
upstream issue so the context is easy to gather:

```text
--TEST--
GH-17383: PDO Firebird connection errors
--EXTENSIONS--
pdo_firebird
--XLEAK--
Firebird leaks when isc_attach_database() is called.
See Firebird issue 7849.
```

### --FLAKY--

Some tests fail randomly even though their relevant code and configuration have not changed. Use
FLAKY when the same test sometimes passes and sometimes fails.

|                  |                                     |
|------------------|-------------------------------------|
| **Mandatory**    | No                                  |
| **Position**     | Before execution section            |
| **Content**      | Plain text                          |
| **Lines**        | One or more                         |
| **Alternatives** | [§](index.md#--xfail--) `--XFAIL--` |
|                  | [§](index.md#--xleak--) `--XLEAK--` |

When a FLAKY test fails, the runner retries it once. A passing retry produces a warning; another
failure remains FAIL. A test that passes on its first run remains PASS and is not retried.

Describe the behaviour that can vary and why a retry is justified. A reason such as `This test
sometimes fails in CI` is not helpful and gives nothing useful to investigate.

#### When **Not** to Use

- If the test fails persistently, use [XFAIL](#--xfail--).
- If the test is flaky in one config, use [SKIPIF](#--skipif--) to output `flaky <reason>`.
- If a condition means the test should not run, use [SKIPIF](#--skipif--).
- If the expected problem is a Valgrind leak, use [XLEAK](#--xleak--).

#### Examples

Timing comparisons can occasionally cross a narrow tolerance. State the exact measurement that
varies:

```text
--TEST--
hrtime() aligns with microtime()
--FLAKY--
Intervals measured by hrtime() and microtime() may differ by more than 5%.
```

Some tests depend on how quickly a workload reaches several timeout stages. Add the relevant
settings so the reason is understandable in context:

```text
--TEST--
Hard timeout reports both time limits
--INI--
max_execution_time=1
hard_timeout=1
--FLAKY--
Processing speed can change whether soft or hard timeout is reached.
```

### --CONFLICTS--

This section is only relevant for parallel test execution (available as of PHP 7.4.0), and allows to
specify conflict keys. While a test that conflicts with key K is running, no other test that
conflicts with K is run. For tests conflicting with "all", no other tests are run in parallel.

An alternative to have a `--CONFLICTS--` section is to add a file named `CONFLICTS` to the
directory containing the tests. The contents of the `CONFLICTS` file must have the same format as
the contents of the `--CONFLICTS--` section.

|               |                              |
|---------------|------------------------------|
| **Mandatory** | No                           |
| **Position**  | Before execution section     |
| **Content**   | Conflict keys, e.g. `server` |
| **Case**      | Sensitive                    |
| **Lines**     | One or more                  |
| **Separator** | Newline                      |

Lines beginning with `#` are comments.

#### Examples

**Example 1**

```text
--CONFLICTS--
server
```

Full example: [conflicts_1.phpt](../samples/index.md#conflicts_1phpt)

### --WHITESPACE_SENSITIVE--

This flag is used to indicate that the test should not be changed by automated
formatting changes. Available as of PHP 7.4.3.

|               |                          |
|---------------|--------------------------|
| **Mandatory** | No                       |
| **Position**  | Before execution section |
| **Content**   | None                     |

### --ARGS--

A single line defining the arguments passed to PHP.

|               |                                   |
|---------------|-----------------------------------|
| **Mandatory** | No                                |
| **Position**  | Before execution section          |
| **Content**   | CLI arguments, e.g. `--arg value` |
| **Lines**     | One                               |

#### Examples

**Example 1**

```text
--ARGS--
--arg value --arg=value -avalue -a=value -a value
```

Full example: [sample010.phpt](../samples/index.md#sample010phpt)

### --CGI--

This section takes no value. It merely provides a simple marker for tests that MUST
be run as CGI, even if there is no `--POST--` or `--GET--` sections in the test file.

|               |                          |
|---------------|--------------------------|
| **Mandatory** | No                       |
| **Position**  | Before execution section |
| **Content**   | None                     |
| **Requires**  | PHP CGI binary           |

#### Examples

**Example 1**

```text
--CGI--
```

Full example: [sample016.phpt](../samples/index.md#sample016phpt)

### --PHPDBG--

This section takes arbitrary phpdbg commands and executes the test file according
to them as it would be run in the phpdbg prompt.

PHPDBG tests may omit an execution source.

|               |                          |
|---------------|--------------------------|
| **Mandatory** | No                       |
| **Position**  | Before execution section |
| **Content**   | phpdbg commands          |
| **Lines**     | One or more              |
| **Requires**  | phpdbg binary            |

#### Examples

**Example 1**

```text
--PHPDBG--
b
4
b
del
0
b
5
r
b
del
1
r
y
q
```

Full example: [phpdbg_1.phpt](../samples/index.md#phpdbg_1phpt)

### --CAPTURE_STDIO--

This section enables which I/O streams the `run-tests.php` test script will use
when comparing executed file to the expected output. The `STDIN` is the standard input stream.
When `STDOUT` is enabled, the test script will also check the contents of the standard output.
`When STDERR` is enabled, the test script will also compare the contents of the standard error I/O
stream.

If this section is left out of the test, by default, all three streams are enabled, so the tests
without this section capture all and is the same as enabling all three manually.

|               |                                           |
|---------------|-------------------------------------------|
| **Mandatory** | No                                        |
| **Position**  | Before execution section                  |
| **Default**   | All streams                               |
| **Content**   | Stream names: `STDIN`, `STDOUT`, `STDERR` |
| **Case**      | Insensitive                               |
| **Lines**     | One or more                               |
| **Separator** | None required                             |

Names are case-insensitive and may be separated by spaces, newlines, or other delimiters.

#### Examples

**Example 1**

```text
--CAPTURE_STDIO--
STDIN STDERR
```

Full example: [capture_stdio_1.phpt](../samples/index.md#capture_stdio_1phpt)

**Example 2**

```text
--CAPTURE_STDIO--
STDIN STDOUT
```

Full example: [capture_stdio_2.phpt](../samples/index.md#capture_stdio_2phpt)

**Example 3**

```text
--CAPTURE_STDIO--
STDIN STDOUT STDERR
```

Full example: [capture_stdio_3.phpt](../samples/index.md#capture_stdio_3phpt)

### --GET--

GET variables to be passed to the test script. This section forces the use of the
CGI binary instead of the usual CLI one.

|               |                                       |
|---------------|---------------------------------------|
| **Mandatory** | No                                    |
| **Position**  | Before execution section              |
| **Content**   | GET variables, e.g. `a=value&b=value` |
| **Lines**     | One                                   |
| **Separator** | `&` between key-value pairs           |
| **Requires**  | PHP CGI binary                        |

#### Examples

**Example 1**

```text
--GET--
a=<b>test</b>&b=http://example.com
```

Full example: [sample001.phpt](../samples/index.md#sample001phpt)

**Example 2**

```text
--GET--
ar[elm1]=1234&ar[elm2]=0660&a=0234
```

Full example: [sample008.phpt](../samples/index.md#sample008phpt)

### --POST--

POST variables or data to be passed to the test script. This section forces the use
of the CGI binary instead of the usual CLI one.

|               |                          |
|---------------|--------------------------|
| **Mandatory** | No                       |
| **Position**  | Before execution section |
| **Content**   | POST body                |
| **Requires**  | PHP CGI binary           |

#### Examples

**Example 1**

```text
--POST--
c=<p>string</p>&d=12345.7
```

Full example: [sample001.phpt](../samples/index.md#sample001phpt)

**Example 2**

```xml
--POST--
<SOAP-ENV:Envelope
  SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
  xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
  xmlns:xsd="http://www.w3.org/2001/XMLSchema"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xmlns:si="http://soapinterop.org/xsd">
  <SOAP-ENV:Body>
        <ns1:test xmlns:ns1="http://testuri.org" />
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
```

Full example: [sample005.phpt](../samples/index.md#sample005phpt)

### --POST_RAW--

Raw POST data to be passed to the test script. This differs from the section above
because it doesn't automatically set the Content-Type, this leaves you free to define your own
within the section. This section forces the use of the CGI binary instead of the usual CLI one.

|               |                          |
|---------------|--------------------------|
| **Mandatory** | No                       |
| **Position**  | Before execution section |
| **Content**   | Raw POST body            |
| **Requires**  | PHP CGI binary           |

#### Examples

**Example 1**

```text
--POST_RAW--
Content-type: multipart/form-data, boundary=AaB03x

--AaB03x content-disposition: form-data; name="field1"

Joe Blow
--AaB03x
content-disposition: form-data; name="pics"; filename="file1.txt"
Content-Type: text/plain

abcdef123456789
--AaB03x--
```

Full example: [sample006.phpt](../samples/index.md#sample006phpt)

### --PUT--

Similar to the section above, PUT data to be passed to the test script. This
section forces the use of the CGI binary instead of the usual CLI one.

|               |                          |
|---------------|--------------------------|
| **Mandatory** | No                       |
| **Position**  | Before execution section |
| **Content**   | Raw PUT body             |
| **Requires**  | PHP CGI binary           |

The body may be preceded by a `Content-Type` header.

#### Examples

**Example 1**

```text
--PUT--
Content-Type: text/json

{"name":"handler","type":0,"flags":112,"level":0,"chunk_size":0,"buffer_size":16384,"buffer_used":3}
```

### --GZIP_POST--

When this section exists, the POST data will be gzencode()'d. This section forces
the use of the CGI binary instead of the usual CLI one.

|               |                                |
|---------------|--------------------------------|
| **Mandatory** | No                             |
| **Position**  | Before execution section       |
| **Content**   | POST body                      |
| **Requires**  | PHP CGI binary, zlib extension |

#### Examples

**Example 1**

```xml
--GZIP_POST--
<SOAP-ENV:Envelope
  SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
  xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
  xmlns:xsd="http://www.w3.org/2001/XMLSchema"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xmlns:si="http://soapinterop.org/xsd">
  <SOAP-ENV:Body>
        <ns1:test xmlns:ns1="http://testuri.org" />
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
```

Full example: [sample005.phpt](../samples/index.md#sample005phpt)

### --DEFLATE_POST--

When this section exists, the POST data will be gzcompress()'ed. This section
forces the use of the CGI binary instead of the usual CLI one.

|               |                                |
|---------------|--------------------------------|
| **Mandatory** | No                             |
| **Position**  | Before execution section       |
| **Content**   | POST body                      |
| **Requires**  | PHP CGI binary, zlib extension |

#### Examples

**Example 1**

```xml
--DEFLATE_POST--
<?xml version="1.0" encoding="ISO-8859-1"?>
<SOAP-ENV:Envelope
  SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
  xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
  xmlns:xsd="http://www.w3.org/2001/XMLSchema"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xmlns:si="http://soapinterop.org/xsd">
  <SOAP-ENV:Body>
        <ns1:test xmlns:ns1="http://testuri.org" />
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
```

Full example: [sample007.phpt](../samples/index.md#sample007phpt)

### --COOKIE--

Cookies to be passed to the test script. This section forces the use of the CGI
binary instead of the usual CLI one.

|               |                                                   |
|---------------|---------------------------------------------------|
| **Mandatory** | No                                                |
| **Position**  | Before execution section                          |
| **Content**   | HTTP cookies, e.g. `hello=World; goodbye=MrChips` |
| **Lines**     | One                                               |
| **Separator** | `;` between cookies                               |
| **Requires**  | PHP CGI binary                                    |

The value must use valid HTTP cookie syntax.

#### Examples

**Example 1**

```
--COOKIE--
hello=World;goodbye=MrChips
```

Full example: [sample002.phpt](../samples/index.md#sample002phpt)

### --STDIN--

Data to be fed to the test script's standard input.

|               |                          |
|---------------|--------------------------|
| **Mandatory** | No                       |
| **Position**  | Before execution section |
| **Content**   | Standard input           |

#### Examples

**Example 1**

```text
--STDIN--
fooBar
use this to input some thing to the php script
```

Full example: [sample009.phpt](../samples/index.md#sample009phpt)

### --FILE--

The test source code.

|                  |                                                     |
|------------------|-----------------------------------------------------|
| **Mandatory**    | Yes, if none of the alternatives is used            |
| **Position**     | After setup and input sections                      |
| **Content**      | PHP code enclosed by PHP tags                       |
| **Alternatives** | [§](index.md#--fileeof--) `--FILEEOF--`             |
|                  | [§](index.md#--file_external--) `--FILE_EXTERNAL--` |
|                  | [§](index.md#--redirecttest--) `--REDIRECTTEST--`   |

#### Examples

**Example 1**

```php
--FILE--
<?php
ini_set('html_errors', false);
var_dump(filter_input(INPUT_GET, "a", FILTER_SANITIZE_STRIPPED));
var_dump(filter_input(INPUT_GET, "b", FILTER_SANITIZE_URL));
var_dump(filter_input(INPUT_GET, "a", FILTER_SANITIZE_SPECIAL_CHARS, array(1,2,3,4,5)));
var_dump(filter_input(INPUT_GET, "b", FILTER_VALIDATE_FLOAT, new stdClass));
var_dump(filter_input(INPUT_POST, "c", FILTER_SANITIZE_STRIPPED, array(5,6,7,8)));
var_dump(filter_input(INPUT_POST, "d", FILTER_VALIDATE_FLOAT));
var_dump(filter_input(INPUT_POST, "c", FILTER_SANITIZE_SPECIAL_CHARS));
var_dump(filter_input(INPUT_POST, "d", FILTER_VALIDATE_INT));
var_dump(filter_var(new stdClass, "d"));
var_dump(filter_input(INPUT_POST, "c", "", ""));
var_dump(filter_var("", "", "", "", ""));
var_dump(filter_var(0, 0, 0, 0, 0));
echo "Done\n";
?>
```

Full example: [sample001.phpt](../samples/index.md#sample001phpt)

### --FILEEOF--

An alternative to `--FILE--` where any trailing line breaks (n || r || rn
found at the end of the section) are omitted. This is an extreme edge-case feature, so 99.99% of the
time you won't need this section.

|                  |                                                     |
|------------------|-----------------------------------------------------|
| **Mandatory**    | Yes, if none of the alternatives is used            |
| **Position**     | After setup and input sections                      |
| **Content**      | PHP code enclosed by PHP tags                       |
| **Alternatives** | [§](index.md#--file--) `--FILE--`                   |
|                  | [§](index.md#--file_external--) `--FILE_EXTERNAL--` |
|                  | [§](index.md#--redirecttest--) `--REDIRECTTEST--`   |

#### Examples

**Example 1**

```php
--FILEEOF--
<?php
eval("echo 'Hello'; // comment");
echo " World";
//last line comment
```

Full example: [sample011.phpt](../samples/index.md#sample011phpt)

### --FILE_EXTERNAL--

An alternative to `--FILE--`. This is used to specify that an external file
should be used as the `--FILE--` contents of the test file, and is designed for running the same
test file with different ini, environment, post/get or other external inputs. Basically, it allows
you to DRY up some of your tests. The file must be in the same directory as the test file, or in a
subdirectory.

|                  |                                                   |
|------------------|---------------------------------------------------|
| **Mandatory**    | Yes, if none of the alternatives is used          |
| **Position**     | After setup and input sections                    |
| **Content**      | Relative path, e.g. `files/file012.inc`           |
| **Lines**        | One                                               |
| **Alternatives** | [§](index.md#--file--) `--FILE--`                 |
|                  | [§](index.md#--fileeof--) `--FILEEOF--`           |
|                  | [§](index.md#--redirecttest--) `--REDIRECTTEST--` |

#### Examples

**Example 1**

```text
--FILE_EXTERNAL--
files/file012.inc
```

Full example: [sample012.phpt](../samples/index.md#sample012phpt)

### --REDIRECTTEST--

This block allows you to redirect from one test to a bunch of other tests. It also
allows you to set configurations which are used on all tests in your destination.

Redirect tests do not use cleanup or expectation sections.

|                  |                                                     |
|------------------|-----------------------------------------------------|
| **Mandatory**    | Yes, if none of the alternatives is used            |
| **Position**     | After setup and input sections                      |
| **Content**      | PHP source returning a configuration array          |
| **Alternatives** | [§](index.md#--file--) `--FILE--`                   |
|                  | [§](index.md#--fileeof--) `--FILEEOF--`             |
|                  | [§](index.md#--file_external--) `--FILE_EXTERNAL--` |

The source is run through `eval()`, and the `TESTS` array key specifies the destination. A
`REDIRECTTEST` cannot be nested inside another `REDIRECTTEST`.

The relative path declared in 'TESTS' is relative to the base directory for the PHP source code, not
relative to the current directory.

Last note, the array in this section must be returned to work.

#### Examples

**Example 1**

```php
--REDIRECTTEST--
return array(
  'ENV' => array(
          'PDOTEST_DSN' => 'sqlite2::memory:'
        ),
  'TESTS' => 'ext/pdo/tests'
  );
```

Full example: [sample013.phpt](../samples/index.md#sample013phpt)

> [!NOTE]
> The destination tests for this example are not included. See the PDO extension tests for
> reference to live tests using this section.

**Example 2**

```php
--REDIRECTTEST--
# magic auto-configuration

$config = array(
  'TESTS' => 'ext/pdo/tests'
);

if (false !== getenv('PDO_MYSQL_TEST_DSN')) {
  # user set them from their shell
  $config['ENV']['PDOTEST_DSN'] = getenv('PDO_MYSQL_TEST_DSN');
  $config['ENV']['PDOTEST_USER'] = getenv('PDO_MYSQL_TEST_USER');
  $config['ENV']['PDOTEST_PASS'] = getenv('PDO_MYSQL_TEST_PASS');
  if (false !== getenv('PDO_MYSQL_TEST_ATTR')) {
        $config['ENV']['PDOTEST_ATTR'] = getenv('PDO_MYSQL_TEST_ATTR');
  }
} else {
  $config['ENV']['PDOTEST_DSN'] = 'mysql:host=localhost;dbname=test';
  $config['ENV']['PDOTEST_USER'] = 'root';
  $config['ENV']['PDOTEST_PASS'] = '';
}

return $config;
```

Full example: [sample014.phpt](../samples/index.md#sample014phpt)

> [!NOTE]
> The destination tests for this example are not included. See the PDO extension tests for
> reference to live tests using this section.

### --CLEAN--

Code that is executed after a test completes. It's main purpose is to allow you to
clean up after yourself. You might need to remove files created during the test or close sockets or
database connections following a test. Infact, even if a test fails or encounters a fatal error
during the test, the code found in the `--CLEAN--` section will still run.

Code in the clean section is run in a completely different process than the one the test was run in.
So do not try accessing variables you created in the `--FILE--` section from inside the
`--CLEAN--` section, they won't exist.

Using the switch `--no-clean` on `run-tests.php`, you can prevent the code found in the
`--CLEAN--` section of a test from running. This allows you to inspect generated data or files
without them being removed by the `--CLEAN--` section.

|               |                                  |
|---------------|----------------------------------|
| **Mandatory** | No                               |
| **Position**  | Before any `--EXPECT*--` section |
| **Content**   | PHP code enclosed by PHP tags    |

#### Examples

**Example 1**

```php
--CLEAN--
<?php
unlink(__DIR__.'/DomDocument_save_basic.tmp');
?>
```

Full example: [sample024.phpt](../samples/index.md#sample024phpt)

**Example 2**

```php
--CLEAN--
<?php
require_once('clean.inc');
?>
```

Full example: [sample025.phpt](../samples/index.md#sample025phpt)

**Example 3**

```php
--CLEAN--
<?php
$key = ftok(__DIR__.'/003.phpt', 'q');
$s = shm_attach($key);
shm_remove($s);
?>
```

Full example: [sample022.phpt](../samples/index.md#sample022phpt)

### --EXPECTHEADERS--

The expected headers. Any header specified here must exist in the response and have
the same value or the test fails. Additional headers found in the actual tests while running are
ignored.

|               |                                                        |
|---------------|--------------------------------------------------------|
| **Mandatory** | No                                                     |
| **Position**  | After `--CLEAN--` or execution, before expected output |
| **Content**   | HTTP headers, e.g. `Content-Type: text/plain`          |
| **Case**      | Sensitive                                              |
| **Lines**     | One or more                                            |
| **Separator** | Newline                                                |
| **Requires**  | PHP CGI binary                                         |

#### Examples

**Example 1**

--EXPECTHEADERS-- Status: 404

**Example 1**

```text
--EXPECTHEADERS--
Content-type: text/html; charset=UTF-8
Status: 403 Access Denied
```

Full example: [sample018.phpt](../samples/index.md#sample018phpt)

> [!NOTE]
> The destination tests for this example are not included. See the phar extension tests for
> reference to live tests using this section.

### --EXPECT--

The expected output from the test script. This must match the actual output from
the test script exactly for the test to pass.

|                  |                                                                   |
|------------------|-------------------------------------------------------------------|
| **Mandatory**    | Yes, if none of the alternatives is used                          |
| **Position**     | Last in file                                                      |
| **Content**      | Plain text                                                        |
| **Alternatives** | [§](index.md#--expectf--) `--EXPECTF--`                           |
|                  | [§](index.md#--expect_external--) `--EXPECT_EXTERNAL--`           |
|                  | [§](index.md#--expectf_external--) `--EXPECTF_EXTERNAL--`         |
|                  | [§](index.md#--expectregex--) `--EXPECTREGEX--`                   |
|                  | [§](index.md#--expectregex_external--) `--EXPECTREGEX_EXTERNAL--` |

#### Examples

**Example 1**

```text
--EXPECT--
array(2) {
  ["hello"]=>
  string(5) "World"
  ["goodbye"]=>
  string(7) "MrChips"
}
```

Full example: [sample002.phpt](../samples/index.md#sample002phpt)

### --EXPECT_EXTERNAL--

Similar to `--EXPECT--` section, but just stating a filename where to load the
expected output from.

|                  |                                                                   |
|------------------|-------------------------------------------------------------------|
| **Mandatory**    | Yes, if none of the alternatives is used                          |
| **Position**     | Last in file                                                      |
| **Content**      | Filename, e.g. `test001.expected.txt`                             |
| **Lines**        | One                                                               |
| **Alternatives** | [§](index.md#--expect--) `--EXPECT--`                             |
|                  | [§](index.md#--expectf--) `--EXPECTF--`                           |
|                  | [§](index.md#--expectf_external--) `--EXPECTF_EXTERNAL--`         |
|                  | [§](index.md#--expectregex--) `--EXPECTREGEX--`                   |
|                  | [§](index.md#--expectregex_external--) `--EXPECTREGEX_EXTERNAL--` |

#### Examples

**Example 1**

```text
--EXPECT_EXTERNAL--
test001.expected.txt
```

*test001.expected.txt*

```php
array(2) {
  ["hello"]=>
  string(5) "World"
  ["goodbye"]=>
  string(7) "MrChips"
}
```

### --EXPECTF--

An alternative of `--EXPECT--`. Where it differs from `--EXPECT--` is that it
uses a number of substitution tags for strings, spaces, digits, etc. that appear in test case output
but which may vary between test runs. The most common example of this is to use %s and %d to match
the file path and line number which are output by PHP Warnings.

|                  |                                                                   |
|------------------|-------------------------------------------------------------------|
| **Mandatory**    | Yes, if none of the alternatives is used                          |
| **Position**     | Last in file                                                      |
| **Content**      | Plain text with substitution tags                                 |
| **Alternatives** | [§](index.md#--expect--) `--EXPECT--`                             |
|                  | [§](index.md#--expect_external--) `--EXPECT_EXTERNAL--`           |
|                  | [§](index.md#--expectf_external--) `--EXPECTF_EXTERNAL--`         |
|                  | [§](index.md#--expectregex--) `--EXPECTREGEX--`                   |
|                  | [§](index.md#--expectregex_external--) `--EXPECTREGEX_EXTERNAL--` |

The tags represent output that may vary between runs or platforms.

The following is a list of all tags and what they are used to represent:

| Tag       | Represents                                                                        |
|-----------|-----------------------------------------------------------------------------------|
| `%e`      | A directory separator, for example / on Linux.                                    |
| `%s`      | One or more of any character or white space except the end of line character.     |
| `%S`      | Zero or more of any character or white space except the end of line character.    |
| `%a`      | One or more of any character or white space including the end of line character.  |
| `%A`      | Zero or more of any character or white space including the end of line character. |
| `%w`      | Zero or more white space characters.                                              |
| `%i`      | A signed integer value, for example +3142, -3142, 3142.                           |
| `%d`      | An unsigned integer value, for example 123456.                                    |
| `%x`      | One or more hexadecimal characters (range 0-9, a-f, A-F).                         |
| `%f`      | A floating point number, for example: 3.142, -3.142, 3.142E-10, 3.142e+10.        |
| `%c`      | A single character of any sort (.).                                               |
| `%r...%r` | Any string (...) enclosed between two `%r` is treated as a regular expression.    |

#### Examples

**Example 1**

```text
--EXPECTF--
string(4) "test"
string(18) "http://example.com"
string(27) "&#60;b&#62;test&#60;/b&#62;"

Notice: Object of class stdClass could not be converted to int in %ssample001.php on line %d
bool(false)
string(6) "string"
float(12345.7)
string(29) "&#60;p&#62;string&#60;/p&#62;"
bool(false)

Warning: filter_var() expects parameter 2 to be long, string given in %s011.php on line %d
NULL

Warning: filter_input() expects parameter 3 to be long, string given in %s011.php on line %d
NULL

Warning: filter_var() expects at most 3 parameters, 5 given in %s011.php on line %d
NULL

Warning: filter_var() expects at most 3 parameters, 5 given in %s011.php on line %d
NULL
Done
```

Full example: [sample001.phpt](../samples/index.md#sample001phpt)

**Example 2**

```text
--EXPECTF--
Warning: bzopen() expects exactly 2 parameters, 0 given in %s on line %d NULL

Warning: bzopen(): '' is not a valid mode for bzopen(). Only 'w' and 'r' are supported. in %s on line %d
bool(false)

Warning: bzopen(): filename cannot be empty in %s on line %d
bool(false)

Warning: bzopen(): filename cannot be empty in %s on line %d
bool(false)

Warning: bzopen(): 'x' is not a valid mode for bzopen(). Only 'w' and 'r' are supported. in %s on line %d
bool(false)

Warning: bzopen(): 'rw' is not a valid mode for bzopen(). Only 'w' and 'r' are supported. in %s on line %d
bool(false)

Warning: bzopen(no_such_file): failed to open stream: No such file or directory in %s on line %d
bool(false)
resource(%d) of type (stream) Done
```

Full example: [sample019.phpt](../samples/index.md#sample019phpt)

**Example 3**

```text
--EXPECTF--
object(DOMNodeList)#%d (0) {
}
int(0)
bool(true)
bool(true)
string(0) ""
bool(true)
bool(true)
bool(false)
bool(false)
```

Full example: [sample020.phpt](../samples/index.md#sample020phpt)

`/ext/standard/tests/strings/str_shuffle.phpt` is a good example for using `EXPECTF` instead of
`EXPECT`. From time to time the algorithm used for shuffle changed and sometimes the machine used
to execute the code has influence on the result of shuffle. But it always returns a three character
string detectable by `%s` (that matches any string until the end of the line). Other scan-able
forms are `%a` for any amount of chars (at least one), `%i` for integers, `%d` for numbers
only, `%f` for floating point values, `%c` for single characters, `%x` for hexadecimal values,
`%w` for any number of whitespace characters and `%e` for `DIRECTORY_SEPARATOR` (`'\'` or
`'/'`).

*/ext/standard/tests/strings/str_shuffle.phpt*

```php
--TEST--
Testing str_shuffle.
--FILE--
<?php
/* Do not change this test it is a README.TESTING example. */
$s = '123';
var_dump(str_shuffle($s));
var_dump($s);
?>
--EXPECTF--
string(3) "%s"
string(3) "123"
```

### --EXPECTF_EXTERNAL--

Similar to `--EXPECTF--` section, but like the `--EXPECT_EXTERNAL--` section
just stating a filename where to load the expected output from.

|                  |                                                                   |
|------------------|-------------------------------------------------------------------|
| **Mandatory**    | Yes, if none of the alternatives is used                          |
| **Position**     | Last in file                                                      |
| **Content**      | Filename, e.g. `test001.expected.txt`                             |
| **Lines**        | One                                                               |
| **Alternatives** | [§](index.md#--expect--) `--EXPECT--`                             |
|                  | [§](index.md#--expectf--) `--EXPECTF--`                           |
|                  | [§](index.md#--expect_external--) `--EXPECT_EXTERNAL--`           |
|                  | [§](index.md#--expectregex--) `--EXPECTREGEX--`                   |
|                  | [§](index.md#--expectregex_external--) `--EXPECTREGEX_EXTERNAL--` |

### --EXPECTREGEX--

An alternative of `--EXPECT--`. This form allows the tester to specify the result in a regular
expression.

|                  |                                                                   |
|------------------|-------------------------------------------------------------------|
| **Mandatory**    | Yes, if none of the alternatives is used                          |
| **Position**     | Last in file                                                      |
| **Content**      | Regular expression                                                |
| **Alternatives** | [§](index.md#--expect--) `--EXPECT--`                             |
|                  | [§](index.md#--expectf--) `--EXPECTF--`                           |
|                  | [§](index.md#--expect_external--) `--EXPECT_EXTERNAL--`           |
|                  | [§](index.md#--expectf_external--) `--EXPECTF_EXTERNAL--`         |
|                  | [§](index.md#--expectregex_external--) `--EXPECTREGEX_EXTERNAL--` |

The pattern may represent output that varies between runs or platforms.

#### Examples

**Example 1**

```text
--EXPECTREGEX--
M_E       : 2.718281[0-9]*
M_LOG2E   : 1.442695[0-9]*
M_LOG10E  : 0.434294[0-9]*
M_LN2     : 0.693147[0-9]*
M_LN10    : 2.302585[0-9]*
M_PI      : 3.141592[0-9]*
M_PI_2    : 1.570796[0-9]*
M_PI_4    : 0.785398[0-9]*
M_1_PI    : 0.318309[0-9]*
M_2_PI    : 0.636619[0-9]*
M_SQRTPI  : 1.772453[0-9]*
M_2_SQRTPI: 1.128379[0-9]*
M_LNPI    : 1.144729[0-9]*
M_EULER   : 0.577215[0-9]*
M_SQRT2   : 1.414213[0-9]*
M_SQRT1_2 : 0.707106[0-9]*
M_SQRT3   : 1.732050[0-9]*
```

Full example: [sample021.phpt](../samples/index.md#sample021phpt)

**Example 2**

```text
--EXPECTF--
*** Testing imap_append() : basic functionality ***
Create a new mailbox for test
Create a temporary mailbox and add 0 msgs
.. mailbox '%s' created
Add a couple of msgs to new mailbox {%s}INBOX.%s
bool(true)
bool(true)
Msg Count after append : 2
List the msg headers
array(2) {
  [0]=>
  string(%d) "%w%s       1)%s webmaster@something. Test message (%d chars)"
  [1]=>
  string(%d) "%w%s       2)%s webmaster@something. Another test (%d chars)"
}
```

Full example: [sample025.phpt](../samples/index.md#sample025phpt)

**Example 3**

```text
--EXPECTREGEX--
string\(4\) \"-012\"
string\(8\) \"2d303132\"
(string\(13\) \"   4294967284\"|string\(20\) \"18446744073709551604\")
(string\(26\) \"20202034323934393637323834\"|string\(40\) \"3138343436373434303733373039353531363034\")
```

Full example: [sample023.phpt](../samples/index.md#sample023phpt)

`/ext/standard/tests/strings/strings001.phpt` is a good example for using `EXPECTREGEX` instead
of `EXPECT`. This test also shows that in `EXPECTREGEX` some characters need to be escaped since
otherwise they would be interpreted as a regular expression.

*/ext/standard/tests/strings/strings001.phpt*

```php
--TEST--
Test whether strstr() and strrchr() are binary safe.
--FILE--
<?php
/* Do not change this test it is a README.TESTING example. */
$s = "alabala nica".chr(0)."turska panica";
var_dump(strstr($s, "nic"));
var_dump(strrchr($s," nic"));
?>
--EXPECTREGEX--
string\(18\) \"nica\x00turska panica\"
string\(19\) \" nica\x00turska panica\"
```

### --EXPECTREGEX_EXTERNAL--

Similar to `--EXPECTREGEX--` section, but like the `--EXPECT_EXTERNAL--`
section just stating a filename where to load the expected output from.

|                  |                                                           |
|------------------|-----------------------------------------------------------|
| **Mandatory**    | Yes, if none of the alternatives is used                  |
| **Position**     | Last in file                                              |
| **Content**      | Filename, e.g. `test001.expected.txt`                     |
| **Lines**        | One                                                       |
| **Alternatives** | [§](index.md#--expect--) `--EXPECT--`                     |
|                  | [§](index.md#--expectf--) `--EXPECTF--`                   |
|                  | [§](index.md#--expect_external--) `--EXPECT_EXTERNAL--`   |
|                  | [§](index.md#--expectf_external--) `--EXPECTF_EXTERNAL--` |
|                  | [§](index.md#--expectregex--) `--EXPECTREGEX--`           |

## Order

`--TEST--` must come first. Other sections are not order-sensitive but should
follow the convention below to make tests easier to scan. Omit sections that do
not apply; choose one where alternatives share a position. Square brackets mark
omittable sections; `|` separates alternatives.

```text
--TEST--
[--DESCRIPTION--]
[--CREDITS--]

[--INI--]
[--ENV--]
[--EXTENSIONS--]

[--SKIPIF--]
[--XFAIL--]
[--XLEAK--]
[--FLAKY--]
[--CONFLICTS--]
[--WHITESPACE_SENSITIVE--]

[--ARGS--]
[--CGI--]
[--PHPDBG--]
[--CAPTURE_STDIO--]

[--GET--]
[--POST-- | --POST_RAW-- | --PUT-- | --GZIP_POST-- | --DEFLATE_POST--]
[--COOKIE--]
[--STDIN--]

[--FILE-- | --FILEEOF-- | --FILE_EXTERNAL-- | --REDIRECTTEST--]
[--CLEAN--]

[--EXPECTHEADERS--]
[--EXPECT-- | --EXPECT_EXTERNAL-- | --EXPECTF-- | --EXPECTF_EXTERNAL-- | --EXPECTREGEX-- | --EXPECTREGEX_EXTERNAL--]
```
