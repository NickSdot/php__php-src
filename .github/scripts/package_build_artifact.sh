#!/usr/bin/env bash

set -euo pipefail

artifact_directory="$RUNNER_TEMP/php-build-artifact"
install_root="$RUNNER_TEMP/php-install-root"
if [[ -e $artifact_directory ]]; then
    echo "Artifact directory already exists: $artifact_directory" >&2
    exit 1
fi
mkdir "$artifact_directory"
if [[ ! -d $install_root ]]; then
    echo "Installed PHP tree does not exist: $install_root" >&2
    exit 1
fi

git -C "$GITHUB_WORKSPACE" rev-parse HEAD > "$artifact_directory/source-sha"
tar --exclude='./.git' -cf "$artifact_directory/workspace.tar" \
    -C "$GITHUB_WORKSPACE" .
tar -cf "$artifact_directory/install-root.tar" -C "$install_root" .

(
    cd "$artifact_directory"
    sha256sum workspace.tar install-root.tar > SHA256SUMS
)
