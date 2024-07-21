#!/usr/bin/env bash

docker-compose build
docker-compose up -d

docker-compose exec db bash init.sh
docker-compose exec app bash tools/setup.sh
