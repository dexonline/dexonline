#!/usr/bin/python
# logrotate doesn't seem to be able to store logs forever and stick the
# year/month/day in their name.
import datetime
import glob
import os
import time

ARCHIVE_DIR = '/var/log/httpd/archive'
patterns = ['/var/log/httpd/dex-access_log.[0-9]',
            '/var/log/httpd/dex-access_log.[0-9][0-9]',
            '/var/log/httpd/dex-error_log.[0-9]',
            '/var/log/httpd/dex-error_log.[0-9][0-9]',
            '/var/log/httpd/dexwiki-access_log.[0-9]',
            '/var/log/httpd/dexwiki-access_log.[0-9][0-9]']
fileNames = []
for pattern in patterns:
    fileNames += glob.glob(pattern)
#endfor

if not os.path.exists(ARCHIVE_DIR):
    os.makedirs(ARCHIVE_DIR)
#endif

for fileName in fileNames:
     modificationTimestamp = os.stat(fileName)[8]
     timeSuffix = datetime.datetime.fromtimestamp(modificationTimestamp). \
                  strftime('%Y-%m-%d')
     lastDot = fileName.rfind('.')
     lastSlash = fileName.rfind('/')
     prefix = fileName[lastSlash + 1 : lastDot]
     newFileName = ARCHIVE_DIR + '/' + prefix + '-' + timeSuffix
     print "%s => %s" % (fileName, newFileName)
     os.rename(fileName, newFileName)
#endfor
