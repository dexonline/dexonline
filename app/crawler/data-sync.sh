#!/bin/bash

# sample rsync script to copy crawler data between two machines
# note that DB tables must be copied separately

SOURCE_PORT=22
SOURCE_HOST=user@host:/path/to/data/
LOCAL_PATH=/path/to/local/folder/

rsync -avz -e "ssh -p $SOURCE_PORT" $SOURCE_HOST $LOCAL_PATH
