#!/usr/bin/env bash

docker-compose build
docker-compose up -d

echo "Starting database initialization..."
docker-compose exec db bash init.sh

echo "Starting application setup..."
docker-compose exec app bash tools/setup.sh
docker-compose exec app bash -c "chmod 666 /var/www/html/Config.php"
docker-compose exec app bash -c "touch /var/log/dexonline.log"
docker-compose exec app bash -c "chmod 666 /var/log/dexonline.log"
docker-compose exec app bash -c "sed -i 's|const DATABASE = .*;|const DATABASE = '\''mysql://root:admin@db.localhost/dexonline'\'';|' Config.php"
docker-compose exec app bash -c "sed -i 's|const URL_PREFIX = .*;|const URL_PREFIX = '\''/'\'';|' Config.php"

echo "Application setup complete."
echo "You can now access the application at http://dex.localhost"
