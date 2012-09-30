#!/bin/sh

# Count number of messages composed by each OTRS user.
# This script must be run on the machine hosting the OTRS database.
# The article_type.name is 'email-external' for incoming and out going messages and 'note-internal' for tickets closed withot a reply.
# The users.id is 1 (root) for incoming messages and 2+ for our responses.

mysql -u root otrs -e " \
  select users.login, count(*) \
    from article, article_type, users \
    where article_type_id = article_type.id \
    and users.id = article.change_by \
    and article_type.name = 'email-external' \
    and users.id != 1 \
    group by users.id \
"
