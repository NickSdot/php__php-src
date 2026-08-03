#!/usr/bin/env bash

set -euo pipefail

artifact_directory="$RUNNER_TEMP/php-build-artifact"
expected_sha=$PHP_BUILD_SOURCE_SHA
artifact_sha=$(<"$artifact_directory/source-sha")

if [[ ! $expected_sha =~ ^[0-9a-f]{40}$
    || ! $artifact_sha =~ ^[0-9a-f]{40}$
    || $artifact_sha != "$expected_sha" ]]; then
    echo "Build artifact source mismatch: expected $expected_sha, got $artifact_sha" >&2
    exit 1
fi

(
    cd "$artifact_directory"
    sha256sum -c SHA256SUMS
)
tar --no-same-owner -xf "$artifact_directory/workspace.tar" \
    -C "$GITHUB_WORKSPACE"
sudo tar --no-same-owner -xf "$artifact_directory/install-root.tar" -C /

if [[ ! -x $GITHUB_WORKSPACE/sapi/cli/php || ! -x /usr/bin/php ]]; then
    echo "Build artifact does not contain the expected PHP executables" >&2
    exit 1
fi
"$GITHUB_WORKSPACE/sapi/cli/php" -n -v
