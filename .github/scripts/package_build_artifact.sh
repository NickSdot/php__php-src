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
tar --exclude='./.git' -cf "$artifact_directory/workspace.tar" \
    -C "$GITHUB_WORKSPACE" .

(
    cd "$artifact_directory"
    sha256sum workspace.tar > SHA256SUMS
)
