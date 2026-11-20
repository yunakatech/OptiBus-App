#!/usr/bin/env bash
set -euo pipefail

check() {
    local path="$1" expected="$2" actual
    actual=$(vercel curl "$path" --deployment "$DEPLOYMENT_ID" --token="$VERCEL_TOKEN" -- --silent --show-error --output /dev/null --write-out '%{http_code}')
    if [[ "$actual" != "$expected" ]]; then
        echo "GET $path: expected $expected, got $actual" >&2
        exit 1
    fi
    echo "GET $path: $actual"
}

check /up 200
check / 200
check /login 200
check /book/cabo-pinrang 200
check /branding/optibus-monogram.svg 200
check /favicon.svg 200

asset=$(node -e 'const m=require("./public/build/manifest.json"); process.stdout.write("/build/" + m["resources/js/app.ts"].file)')
check "$asset" 200

post=$(vercel curl /up --deployment "$DEPLOYMENT_ID" --token="$VERCEL_TOKEN" -- --silent --show-error --request POST --data 'probe=1' --output /dev/null --write-out '%{http_code}')
if [[ "$post" != 405 && "$post" != 419 ]]; then
    echo "POST /up: expected Laravel 405 or 419, got $post" >&2
    exit 1
fi
echo "POST /up: $post"
