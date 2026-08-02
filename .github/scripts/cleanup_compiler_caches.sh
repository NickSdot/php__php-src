#!/usr/bin/env bash

set -euo pipefail

cache_file=$(mktemp)
trap 'rm -f "$cache_file"' EXIT

gh api --paginate --slurp "/repos/$GITHUB_REPOSITORY/actions/caches?per_page=100" > "$cache_file"

jq -r '
    [.[].actions_caches[]
        | select(.key | test("^(ccache-)?php-compiler-v3-.*-[0-9a-f]{40}-?$"))]
    | group_by(.ref + "\u0000" + (.key | sub("-[0-9a-f]{40}-?$"; "")))
    | .[]
    | sort_by(.created_at)
    | reverse
    | .[1:]
    | .[].id
' "$cache_file" | while read -r cache_id; do
    gh api --method DELETE "/repos/$GITHUB_REPOSITORY/actions/caches/$cache_id"
done
