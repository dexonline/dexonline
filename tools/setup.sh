#!/bin/bash
#
# Configuration script to be run when a new client is first checked out

FULL_NAME=`readlink -e $0`
TOOLS_DIR=`dirname $FULL_NAME`
ROOT_DIR=`dirname $TOOLS_DIR`
cd $ROOT_DIR
echo "The root of your client appears to be $ROOT_DIR"

# Create a copy of the config file unless it already exists
if [ ! -e dex.conf ]; then
  echo "* copying dex.conf.sample to dex.conf"
  cp dex.conf.sample dex.conf
else
  echo "* dex.conf already exists, skipping"
fi

# Create a copy of the .htaccess file unless it already exists
if [ ! -e wwwbase/.htaccess ]; then
  echo "* copying wwwbase/.htaccess.sample to wwwbase/.htaccess"
  cp wwwbase/.htaccess.sample wwwbase/.htaccess
else
  echo "* wwwbase/.htaccess already exists, skipping"
fi

# Make the logs and the Smarty compiled templates directory world-writable
echo "* running chmod on the log/ directory and files"
chmod 777 log
touch log/userlog
chmod 666 log/userlog
touch log/dictlog
chmod 666 log/dictlog
touch log/scriptlog
chmod 666 log/scriptlog
touch log/wotdelflog
chmod 666 log/wotdelflog
touch log/visuallog
chmod 666 log/visuallog

echo "* running chmod on the templates_c directory"
chmod 777 templates_c

# Make all directories under wwwbase/img/wotd/ world-writable
echo "* making some directories under wwwbase/img/ world-writable"
find wwwbase/img/wotd/ -type d | xargs chmod 777
find wwwbase/img/visual/ -type d | xargs chmod 777

# Allow user avatar uploads under wwwbase/img/user
chmod 777 wwwbase/img/user

