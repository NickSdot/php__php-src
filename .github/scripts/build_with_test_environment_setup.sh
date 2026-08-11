#!/usr/bin/env bash

set -euo pipefail

started_at=$SECONDS
setup_log="$RUNNER_TEMP/php-test-environment-setup.log"
./.github/scripts/setup-x64.sh >"$setup_log" 2>&1 &
setup_pid=$!

set +e
make -j"$(/usr/bin/nproc)" >/dev/null
build_status=$?
build_finished_at=$SECONDS
wait "$setup_pid"
setup_status=$?
set -e

cat "$setup_log"
printf 'PHP build duration: %dm %ds\n' \
    "$(((build_finished_at - started_at) / 60))" "$(((build_finished_at - started_at) % 60))"
printf 'Test environment wait after PHP build: %dm %ds\n' \
    "$(((SECONDS - build_finished_at) / 60))" "$(((SECONDS - build_finished_at) % 60))"

if [[ $build_status -ne 0 ]]; then
    echo "PHP build exited with code $build_status" >&2
    exit "$build_status"
fi
if [[ $setup_status -ne 0 ]]; then
    echo "Test environment setup exited with code $setup_status" >&2
    exit "$setup_status"
fi
