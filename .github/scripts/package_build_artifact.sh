#!/usr/bin/env bash

set -euo pipefail

artifact_directory="$RUNNER_TEMP/php-build-artifact"
if [[ -e $artifact_directory ]]; then
    echo "Artifact directory already exists: $artifact_directory" >&2
    exit 1
fi
mkdir "$artifact_directory"

if [[ ! $PHP_BUILD_SOURCE_SHA =~ ^[0-9a-f]{40}$ ]]; then
    echo "Invalid source commit: $PHP_BUILD_SOURCE_SHA" >&2
    exit 1
fi
printf '%s\n' "$PHP_BUILD_SOURCE_SHA" > "$artifact_directory/source-sha"

required_executables=(
    sapi/cgi/php-cgi
    sapi/cli/php
    sapi/fpm/php-fpm
    sapi/phpdbg/phpdbg
)
for executable in "${required_executables[@]}"; do
    if [[ ! -x $GITHUB_WORKSPACE/$executable ]]; then
        echo "Build artifact is missing $executable" >&2
        exit 1
    fi
done

# Test jobs need the linked executables and shared modules, but not the
# intermediate objects and archives used to produce them.
tar --exclude='./.git' \
    --exclude='*.a' \
    --exclude='*.la' \
    --exclude='*.lo' \
    --exclude='*.o' \
    -cf "$artifact_directory/workspace.tar" \
    -C "$GITHUB_WORKSPACE" .

printf 'Packaged workspace size: '
du -h "$artifact_directory/workspace.tar" | cut -f1

(
    cd "$artifact_directory"
    sha256sum workspace.tar > SHA256SUMS
)
