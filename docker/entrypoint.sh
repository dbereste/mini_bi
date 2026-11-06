#!/bin/sh
set -e
cd /var/www/html

# When the source is bind-mounted from the host, make sure Laravel can write.
[ -f .env ] || cp .env.example .env
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R a+rwX storage bootstrap/cache 2>/dev/null || true
chown www-data:www-data /data /data/database.sqlite 2>/dev/null || true

exec "$@"
