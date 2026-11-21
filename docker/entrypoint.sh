#!/bin/sh
set -eu

data_dir=/var/lib/handmade
storage_dir="$data_dir/storage"

mkdir -p "$storage_dir/app/private" "$storage_dir/framework/cache/data" \
    "$storage_dir/framework/sessions" "$storage_dir/framework/views" "$storage_dir/logs"

if [ ! -f "$data_dir/.env" ]; then
    umask 077
    env_file=$(mktemp "$data_dir/.env.XXXXXX")
    trap 'rm -f "$env_file"' EXIT
    key=$(php -r 'echo "base64:".base64_encode(random_bytes(32));')

    cat > "$env_file" <<ENV
APP_NAME="Handmade Work"
APP_ENV=local
APP_DEBUG=true
APP_KEY=$key
ENV

    # Keep the first complete configuration if two instances start together.
    if ! ln "$env_file" "$data_dir/.env" && [ ! -f "$data_dir/.env" ]; then
        exit 1
    fi
    rm -f "$env_file"
    trap - EXIT
fi

CACHE_STORE=file php artisan migrate --force --isolated=75
exec "$@"
