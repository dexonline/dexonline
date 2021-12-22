#!/bin/bash
#
# CSS compilation script to be run when modifying www/scss/main.scss.
cd `dirname $0`
CWD=`pwd`
ROOT_DIR=`dirname $CWD`
cd $ROOT_DIR
echo "The root of your client appears to be $ROOT_DIR"

export SASS_PATH=www/scss/third-party/bootstrap/

# 1. Compile the compressed light theme
INPUT=www/scss/main-light.scss
OUTPUT=www/css/third-party/bootstrap.min.css
TYPE=compressed
echo "Compiling $INPUT into $OUTPUT ($TYPE)"
sassc -t $TYPE $INPUT > $OUTPUT

# 2. Compile the expanded light theme
INPUT=www/scss/main-light.scss
OUTPUT=/tmp/bootstrap-light.css
TYPE=expanded
echo "Compiling $INPUT into $OUTPUT ($TYPE)"
sassc -t $TYPE $INPUT > $OUTPUT

# 3. Compile the expanded dark theme
INPUT=www/scss/main-dark.scss
OUTPUT=/tmp/bootstrap-dark.css
TYPE=expanded
echo "Compiling $INPUT into $OUTPUT ($TYPE)"
sassc -t $TYPE $INPUT > $OUTPUT

# 4. Diff the expanded files
FROM=/tmp/bootstrap-light.css
TO=/tmp/bootstrap-dark.css
OUTPUT=www/css/third-party/bootstrap-diff.css
echo "Diffing $FROM and $TO into $OUTPUT"
php tools/cssDiff.php -f $FROM -t $TO -p 'html.dark' -r -w > $OUTPUT

# Note: do not minify bootstrap-diff.css. It saves 4 KB in unzipped form, but
# nothing in zipped form.

# 5. Cleanup
rm $FROM $TO
