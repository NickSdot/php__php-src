#!/usr/bin/env bash

set -euo pipefail

artifact_name=$PHP_BUILD_ARTIFACT_NAME
expected_sha=$PHP_BUILD_SOURCE_SHA
producer_job=$PHP_BUILD_PRODUCER_JOB
timeout_seconds=${PHP_BUILD_ARTIFACT_TIMEOUT_SECONDS:-900}

if [[ ! $artifact_name =~ ^[A-Za-z0-9._-]+$ ]]; then
    echo "Invalid build artifact name: $artifact_name" >&2
    exit 1
fi
if [[ ! $expected_sha =~ ^[0-9a-f]{40}$ ]]; then
    echo "Invalid source commit: $expected_sha" >&2
    exit 1
fi
if [[ ! $producer_job =~ ^[A-Za-z0-9._-]+$ ]]; then
    echo "Invalid build producer job: $producer_job" >&2
    exit 1
fi
if [[ ! $GITHUB_REPOSITORY =~ ^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$ ]]; then
    echo "Invalid GitHub repository: $GITHUB_REPOSITORY" >&2
    exit 1
fi
if [[ ! $GITHUB_RUN_ID =~ ^[0-9]+$ ]]; then
    echo "Invalid GitHub workflow run ID: $GITHUB_RUN_ID" >&2
    exit 1
fi
if [[ ! $timeout_seconds =~ ^[0-9]+$ || $timeout_seconds -lt 60 || $timeout_seconds -gt 1800 ]]; then
    echo "Invalid build artifact timeout: $timeout_seconds" >&2
    exit 1
fi
: "${GH_TOKEN:?GH_TOKEN is required to download the build artifact}"

api_request() {
    curl --fail-with-body --silent --show-error \
        --header "Authorization: Bearer $GH_TOKEN" \
        --header "Accept: application/vnd.github+json" \
        --header "X-GitHub-Api-Version: 2022-11-28" \
        "https://api.github.com/$1"
}

artifacts_endpoint="repos/$GITHUB_REPOSITORY/actions/runs/$GITHUB_RUN_ID/artifacts?per_page=100&name=$artifact_name"
jobs_endpoint="repos/$GITHUB_REPOSITORY/actions/runs/$GITHUB_RUN_ID/jobs?filter=all&per_page=100"
started_at=$SECONDS
poll_count=0
artifact_id=
artifact_size=

echo "Waiting up to $timeout_seconds seconds for $artifact_name from this workflow run"
while (( SECONDS - started_at < timeout_seconds )); do
    artifacts=$(api_request "$artifacts_endpoint")
    artifact_count=$(jq --arg name "$artifact_name" \
        '[.artifacts[] | select(.name == $name and (.expired | not))] | length' <<<"$artifacts")
    if (( artifact_count > 1 )); then
        echo "Multiple artifacts named $artifact_name exist in this workflow run" >&2
        exit 1
    fi
    if (( artifact_count == 1 )); then
        artifact_sha=$(jq -r --arg name "$artifact_name" \
            '.artifacts[] | select(.name == $name and (.expired | not)) | .workflow_run.head_sha' <<<"$artifacts")
        if [[ $artifact_sha != "$expected_sha" ]]; then
            echo "Build artifact source mismatch: expected $expected_sha, got $artifact_sha" >&2
            exit 1
        fi
        artifact_id=$(jq -r --arg name "$artifact_name" \
            '.artifacts[] | select(.name == $name and (.expired | not)) | .id' <<<"$artifacts")
        artifact_size=$(jq -r --arg name "$artifact_name" \
            '.artifacts[] | select(.name == $name and (.expired | not)) | .size_in_bytes' <<<"$artifacts")
        break
    fi

    if (( poll_count % 3 == 0 )); then
        jobs=$(api_request "$jobs_endpoint")
        producer_count=$(jq --arg name "$producer_job" \
            '[.jobs[] | select(.name == $name or (.name | endswith(" / " + $name)))] | length' <<<"$jobs")
        if (( producer_count > 1 )); then
            echo "Multiple jobs match build producer $producer_job" >&2
            exit 1
        fi
        if (( producer_count == 1 )); then
            producer_status=$(jq -r --arg name "$producer_job" \
                '.jobs[] | select(.name == $name or (.name | endswith(" / " + $name))) | .status' <<<"$jobs")
            producer_conclusion=$(jq -r --arg name "$producer_job" \
                '.jobs[] | select(.name == $name or (.name | endswith(" / " + $name))) | .conclusion // ""' <<<"$jobs")
            if [[ $producer_status == completed && $producer_conclusion != success ]]; then
                echo "Build producer $producer_job finished with $producer_conclusion" >&2
                exit 1
            fi
        fi
    fi

    sleep 5
    ((poll_count += 1))
done

if [[ -z $artifact_id ]]; then
    echo "Timed out waiting for build artifact $artifact_name" >&2
    exit 1
fi

artifact_directory="$RUNNER_TEMP/php-build-artifact"
artifact_archive="$RUNNER_TEMP/php-build-artifact.zip"
if [[ -e $artifact_directory || -e $artifact_archive ]]; then
    echo "Build artifact destination already exists" >&2
    exit 1
fi
mkdir "$artifact_directory"

curl --fail-with-body --silent --show-error --location \
    --header "Authorization: Bearer $GH_TOKEN" \
    --header "Accept: application/vnd.github+json" \
    --header "X-GitHub-Api-Version: 2022-11-28" \
    --output "$artifact_archive" \
    "https://api.github.com/repos/$GITHUB_REPOSITORY/actions/artifacts/$artifact_id/zip"
unzip -q "$artifact_archive" -d "$artifact_directory"
rm "$artifact_archive"

PHP_BUILD_SOURCE_SHA=$expected_sha .github/scripts/restore_build_artifact.sh

printf 'Build artifact wait, download, and restore duration: %dm %ds\n' \
    "$(((SECONDS - started_at) / 60))" "$(((SECONDS - started_at) % 60))"
printf 'Compressed artifact size: %.1f MiB\n' "$((artifact_size / 1048576))"
