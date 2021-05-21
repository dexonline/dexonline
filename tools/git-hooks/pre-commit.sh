#!/bin/bash

# Checks whether there are significant changes to the config files and whether
# the SCSS needs to be recompiled into CSS.

php tools/checkSampleFiles.php && \
  tools/checkScssAge.sh
