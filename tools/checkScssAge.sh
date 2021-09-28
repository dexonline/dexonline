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

INPUTS=(www/scss/common.scss www/scss/main-light.scss www/scss/main-dark.scss)
OUTPUTS=(www/css/third-party/bootstrap.min.css www/css/third-party/bootstrap-diff.css)

for input in ${INPUTS[@]}; do
  git diff --cached --quiet $input
  input_staged=$?

  for output in ${OUTPUTS[@]}; do
    if [ $input_staged = 1 ] && [ $input -nt $output ]; then
      error "$input is newer than $output; please run tools/recompileCss.sh and add $output to the commit"
    fi
  done
done
