#!/bin/bash

DB=$1

# Dump the master branch schema
mysqldump -u root -d DEX > /tmp/dex_schema.sql

# Dump the old version data
mysqldump -u root $DB > /tmp/dump.sql

# Grow the old version schema to match the master schema
# (this will wipe all data).
mysql -u root $DB < /tmp/dex_schema.sql

# Reimport the data
mysql -u root $DB < /tmp/dump.sql

# Set the schema version
mysql -u root $DB -e "insert into Variable set name = 'Schema.version', value = '00156'"

# Cleanup
rm /tmp/dump.sql
