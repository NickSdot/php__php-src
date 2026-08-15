PHP_ARG_ENABLE([coverage-fixture],
  [whether to enable the coverage fixture],
  [AS_HELP_STRING([--enable-coverage-fixture],
    [Enable the coverage fixture])])

if test "$PHP_COVERAGE_FIXTURE" != "no"; then
  PHP_NEW_EXTENSION([coverage_fixture], [coverage_fixture.c], [$ext_shared])
fi
