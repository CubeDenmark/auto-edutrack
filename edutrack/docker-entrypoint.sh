#!/bin/bash
set -e

# docker-entrypoint.sh
# if [ ! -f /var/www/html/.env ]; then
#     cp /var/www/html/.env.example /var/www/html/.env
# fi

# Run database migrations automatically

php artisan migrate:fresh --force
# php artisan migrate --force

php artisan db:seed --class=UserSeeder
php artisan db:seed --class=ClassSeeder

php artisan config:clear
php artisan cache:clear
php artisan config:cache


# Start Apache in foreground
exec apachectl -D FOREGROUND


# # v1.1 Working
# #!/bin/bash
# set -e

# # Run database migrations automatically
# php artisan migrate --force

# # Start Apache in foreground
# exec apachectl -D FOREGROUND




# # v1.0 not working
# #!/bin/sh

# # Exit on any error
# set -e

# # Wait for the database to be ready
# echo "Waiting for database connection..."
# until mysql -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1"; do
#   sleep 2
# done
# echo "Database is ready!"

# # Run migrations and seed the database
# echo "Running migrations..."
# php artisan migrate --force

# # Set proper permissions
# chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# # Start Apache
# exec apache2-foreground
