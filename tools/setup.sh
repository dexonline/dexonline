#!/bin/bash
#
# Configuration script to be run when a new client is first checked out

# for OS X compatibility, do not use readlink
cd `dirname $0`
CWD=`pwd`
ROOT_DIR=`dirname $CWD`
cd $ROOT_DIR
echo "The root of your client appears to be $ROOT_DIR"

# Create a copy of the config file unless it already exists
if [ ! -e Config.php ]; then
  echo "* copying Config.php.sample to Config.php"
  cp Config.php.sample Config.php
else
  echo "* Config.php already exists, skipping"
fi

# Create a copy of the .htaccess file unless it already exists
if [ ! -e wwwbase/.htaccess ]; then
  echo "* copying wwwbase/.htaccess.sample to wwwbase/.htaccess"
  cp wwwbase/.htaccess.sample wwwbase/.htaccess
else
  echo "* wwwbase/.htaccess already exists, skipping"
fi

# Make the Smarty compiled templates directory world-writable
echo "* making some directories and files world-writable"
chmod 777 templates_c

# Allow the webserver to store images here (e.g. for Elfinder thumbs).
chmod 777 wwwbase/img/generated

# Allow PHP scripts to generate merged CSS/JS files
chmod 777 wwwbase/css/merged wwwbase/js/merged

# Compile the native Levenshtein binary
if [ ! -e phplib/c/levenshtein ]; then
  echo "* compiling phplib/c/levenshtein"
  gcc -O2 -Wall -o phplib/c/levenshtein phplib/c/levenshtein.c
  strip phplib/c/levenshtein
else
  echo "* phplib/c/levenshtein already exists, skipping"
fi

# Symlink hooks unless they already exist
if [ ! -e .git/hooks/pre-commit ]; then
  echo "* symlinking tools/git-hooks/pre-commit.php as .git/hooks/pre-commit"
  ln -s $ROOT_DIR/tools/git-hooks/pre-commit.php .git/hooks/pre-commit
else
  echo "* .git/hooks/pre-commit already exists, skipping"
fi

if [ ! -e .git/hooks/post-merge ]; then
  echo "* symlinking tools/git-hooks/post-merge.sh as .git/hooks/post-merge"
  ln -s $ROOT_DIR/tools/git-hooks/post-merge.sh .git/hooks/post-merge
else
  echo "* .git/hooks/post-merge already exists, skipping"
fi
