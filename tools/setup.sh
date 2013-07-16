#!/bin/bash
#
# Configuration script to be run when a new client is first checked out

# Create a copy of the config file unless it already exists
if [ ! -e dex.conf ]
then
  cp dex.conf.sample dex.conf
fi

# Make the logs and the Smarty compiled templates directory world-writable
chmod 777 log
touch log/userlog
chmod 666 log/userlog
touch log/dictlog
chmod 666 log/dictlog
touch log/scriptlog
chmod 666 log/scriptlog
touch log/wotdelflog
chmod 666 log/wotdelflog
chmod 777 templates_c

# Make all directories under wwwbase/img/wotd/ world-writable
find wwwbase/img/wotd/ -type d -not -regex ".*svn.*" | xargs chmod 777

# Allow user avatar uploads under wwwbase/img/user
chmod 777 wwwbase/img/user

if [ ! -e wwwbase/.htaccess ]
then
  cp docs/.htaccess wwwbase/
fi
