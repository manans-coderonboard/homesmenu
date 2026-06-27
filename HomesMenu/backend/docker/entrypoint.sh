#!/usr/bin/env sh
set -e

cd /var/www

# Ensure an app key exists
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
  php artisan key:generate --force
fi

# Wait for MySQL to accept connections
echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT} ..."
until php -r "try { new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}'); } catch (Throwable \$e) { exit(1); }" 2>/dev/null; do
  sleep 2
done
echo "MySQL is ready."

# Migrate
php artisan migrate --force

# Seed only once (when the users table is empty)
USERS=$(php -r "try { \$p=new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}'); echo \$p->query('select count(*) from users')->fetchColumn(); } catch (Throwable \$e) { echo 0; }")
if [ "$USERS" = "0" ]; then
  echo "Seeding database ..."
  php artisan db:seed --force
fi

php artisan storage:link 2>/dev/null || true
php artisan config:clear 2>/dev/null || true

echo "Starting API on http://0.0.0.0:8000"
exec php artisan serve --host=0.0.0.0 --port=8000
