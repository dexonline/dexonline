#!/bin/bash
#
# Pre-commit script that throws an error if the SCSS file is staged for commit
# and is newer than the generated CSS file.

function error {
  echo "The pre-commit hook encountered an error."
  echo "If you know what you are doing, you can bypass this error by using the -n (--no-verify) flag:"
  echo
  echo "    git commit -n"
  echo
  echo "The error message was:"
  echo
  echo $1
  exit 1
}

cd `dirname $0`
CWD=`pwd`
ROOT_DIR=`dirname $CWD`
cd $ROOT_DIR

INPUT=www/scss/main.scss
OUTPUT=www/css/third-party/bootstrap.min.css

git diff --cached --quiet $INPUT
INPUT_STAGED=$?

if [ $INPUT_STAGED = 1 ] && [ $INPUT -nt $OUTPUT ]; then
  error "$INPUT is newer than $OUTPUT; please run scripts/recompileCss.sh and add $OUTPUT to the commint"
fi
