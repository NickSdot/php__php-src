#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_coverage_fixture.h"

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_coverage_fixture, 0, 1, IS_LONG, 0)
    ZEND_ARG_TYPE_INFO(0, enabled, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_FUNCTION(coverage_fixture)
{
    bool enabled;
    zend_long result;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_BOOL(enabled)
    ZEND_PARSE_PARAMETERS_END();

    if (enabled) {
        result = 1;
    } else {
        result = 0;
    }

    RETURN_LONG(result);
}

static const zend_function_entry coverage_fixture_functions[] = {
    PHP_FE(coverage_fixture, arginfo_coverage_fixture)
    PHP_FE_END
};

zend_module_entry coverage_fixture_module_entry = {
    STANDARD_MODULE_HEADER,
    "coverage_fixture",
    coverage_fixture_functions,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    PHP_VERSION,
    STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_COVERAGE_FIXTURE
ZEND_GET_MODULE(coverage_fixture)
#endif
