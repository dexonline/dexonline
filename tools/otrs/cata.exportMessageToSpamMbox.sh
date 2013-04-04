#!/bin/sh
# This script is used in conjunction with the OTRS Generic Agent "Export spam tickets to mbox"
# The idea is that human operators of OTRS can move spam tickets to the Spam queue (and move non-spam to an appropriate queue).
# A Generic Agent will periodically export spam tickets to an Mbox and delete the tickets.
# Then we can run sa-learn to train spam assassin on this mbox.

TICKETID=$2
MBOX="/opt/otrs/mail/spam"

mysql -u root otrs --silent --skip-column-names --raw -e "select article_plain.body from article_plain, article where article_plain.article_id = article.id and a_from not like '%@dexonline.ro%' and article.ticket_id = $TICKETID" >> $MBOX
