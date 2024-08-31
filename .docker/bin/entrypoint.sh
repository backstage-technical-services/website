#!/usr/bin/env bash
set -eo pipefail

function log() {
    echo "caller=entrypoint level=info msg=\"${1}\""
}

log "Running migrations"
php artisan migrate --force --no-interaction

log "Caching config"
php artisan config:cache

log "Starting supervisord"
exec "$@"
