#!/bin/bash
# This script sets up the WordPress testing environment using Docker Compose.

# Exit immediately if a command exits with a non-zero status.
set -e

# Define variables
COMPOSE_FILE=".jules/docker-compose.yml"
WP_CONTAINER_NAME="lineconnect-wordpress"
WP_SERVICE_NAME="wordpress"

echo "--- Setting up Jules' WordPress testing environment ---"

# 1. Cleanup any previous Docker setup (optional, for a clean start)
echo "1. Cleaning up previous Docker containers and volumes (if any)..."
docker compose -f "$COMPOSE_FILE" down --volumes || true # '--volumes' removes named volumes, '|| true' prevents script from exiting if nothing to remove

# 2. Build Docker images
echo "2. Building Docker images..."
docker compose -f "$COMPOSE_FILE" build

# 3. Start Docker containers
echo "3. Starting Docker containers (db and wordpress)..."
docker compose -f "$COMPOSE_FILE" up -d

# Wait for MySQL to be ready
echo "Waiting for MySQL database to be ready..."
until docker compose -f "$COMPOSE_FILE" exec db mysqladmin ping -h"localhost" --silent; do
  echo "MySQL is unavailable - sleeping"
  sleep 2
done
echo "MySQL is up and running!"

# 4. Install WordPress via WP-CLI
docker compose -f "$COMPOSE_FILE" exec -w /var/www/html/ "$WP_SERVICE_NAME" sh -lc "
  wp core install \
    --url='http://localhost' \
    --title='WP Test' \
    --admin_user='admin' \
    --admin_password='password' \
    --admin_email='admin@example.com' \
    --allow-root && \
  wp language core install ja --activate --allow-root && \
  wp option update timezone_string 'Asia/Tokyo' --allow-root && \
  wp option update date_format 'Y-m-d' --allow-root && \
  wp option update time_format 'H:i' --allow-root && \
  wp plugin delete hello.php --allow-root && \
  wp plugin delete akismet --allow-root
"

# 5. Execute install-wp-tests-for-jules.sh inside the wordpress container
echo "5. Running install-wp-tests-for-jules.sh inside the wordpress container..."
# The script expects to be run from the plugin root, which is /app in the container
docker compose -f "$COMPOSE_FILE" exec -w /app "$WP_SERVICE_NAME" sh -lc "bash .jules/install-wp-tests-for-jules.sh"

# 6. Install PHP dependencies with Composer (if needed)
echo "6. Installing PHP dependencies with Composer..."
docker compose -f "$COMPOSE_FILE" exec -w /app "$WP_SERVICE_NAME" sh -lc "composer install"

# 7. Run PHPUnit tests
echo "7. Running PHPUnit tests inside the wordpress container..."
# Adjust the path to phpunit.xml and the tests directory as needed
# Assuming phpunit.xml is in /app and tests are in /app/tests/
docker compose -f "$COMPOSE_FILE" exec -w /app "$WP_SERVICE_NAME" sh -lc "vendor/bin/phpunit --configuration phpunit.xml"

echo "--- Testing environment setup and tests completed ---"

# Optional: Keep containers running for inspection or tear down immediately
# If you want to keep them running, comment out the next line.
# echo "7. Tearing down Docker containers..."
# docker compose -f "$COMPOSE_FILE" down --volumes
