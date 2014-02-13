#!/bin/bash
#
# Kudos http://serverfault.com/questions/272639/checking-if-process-has-not-been-running-for-a-certain-period-of-time
#
# If Varnish is down, touch a file.
# If Varnish is down and the file exists, restart Varnish and delete the file.

check_file=/tmp/varnishChecker.tmp

pid=$(ps -ewwo args | grep [v]arnishd)
if [ -z "$pid" ]
then
    # Varnish is dead
    if [ -f "$check_file" ]
    then
        echo "Încerc să repornesc Varnish."
        service varnish start && rm -f "$check_file"
    else
        echo "Varnish este mort! Creez fișierul."
        touch "$check_file"
    fi
else
    # Varnish is alive
    rm -f "$check_file"
fi
