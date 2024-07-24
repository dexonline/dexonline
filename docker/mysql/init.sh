#!/bin/sh
# Wait for MySQL to start
until mysql -uroot -padmin -e 'SELECT 1'; do
  echo "Waiting for MySQL to start..."
  sleep 1
done

# Create the database
mysql -uroot -padmin -e "create database dexonline charset utf8mb4 collate utf8mb4_romanian_ci"

# Download the database
wget -O /tmp/dex-database.sql.gz https://dexonline.ro/static/download/dex-database.sql.gz

echo "Decompressing the database... this will take a while, please be patient."
# Decompress the data
gzip -d /tmp/dex-database.sql.gz

echo "Importing the database... this will take a while, please be patient."
# Import the data
pv /tmp/dex-database.sql | mysql -uroot -padmin dexonline
