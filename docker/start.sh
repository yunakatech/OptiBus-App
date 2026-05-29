#!/usr/bin/env sh
set -eu

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY belum diisi. Isi APP_KEY di Render sebelum start." >&2
    exit 1
fi

mkdir -p \
    bootstrap/cache \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

if [ ! -L public/storage ]; then
    php artisan storage:link >/dev/null 2>&1 || true
fi

php artisan optimize:clear >/dev/null 2>&1 || true
php artisan config:cache

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    attempt=1

    while [ "$attempt" -le 5 ]; do
        if php artisan migrate --force; then
            break
        fi

        if [ "$attempt" -eq 5 ]; then
            echo "Migration gagal setelah 5 percobaan." >&2
            exit 1
        fi

        echo "Migration gagal. Coba lagi dalam 5 detik..." >&2
        attempt=$((attempt + 1))
        sleep 5
    done
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
