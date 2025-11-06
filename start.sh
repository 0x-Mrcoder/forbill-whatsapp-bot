#!/bin/bash

# Wait for database to be ready
echo "Waiting for database to be ready..."
while ! php artisan tinker --execute="DB::connection()->getPdo();" 2>/dev/null; do
    echo "Database not ready, waiting 2 seconds..."
    sleep 2
done

echo "Database is ready, running migrations..."
php artisan migrate:fresh --seed --force

echo "Starting application..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}