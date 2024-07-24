#!/usr/bin/env bash

docker-compose build
docker-compose up -d

docker-compose exec db bash init.sh

docker-compose exec app bash tools/setup.sh
docker-compose exec app bash -c "chmod 666 /var/www/html/Config.php"
docker-compose exec app bash -c "touch /var/log/dexonline.log"
docker-compose exec app bash -c "chmod 666 /var/log/dexonline.log"
docker-compose exec app bash -c "sed -i 's|const DATABASE = .*;|const DATABASE = '\''mysql://root:admin@db/dexonline'\'';|' Config.php"
docker-compose exec app bash -c "sed -i 's|const URL_PREFIX = .*;|const URL_PREFIX = '\''/'\'';|' Config.php"
