#!/bin/sh

# Wait for database to be ready (already handled by healthcheck, but just in case)
echo "Waiting for database..."

# Warm up the cache in the background (non-blocking)
if [ "$APP_ENV" != "prod" ]; then
    echo "Warming up cache in background..."
    (php bin/console cache:warmup > /dev/null 2>&1 &)
fi

# Execute the main command (php-fpm)
echo "Starting PHP-FPM..."
exec "$@"
