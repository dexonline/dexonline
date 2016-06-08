#!/bin/bash

cat << 'EOQ' | mysql -vvv -uroot DEX
DROP TABLE IF EXISTS tmpRandomWord;

CREATE TABLE IF NOT EXISTS tmpRandomWord LIKE RandomWord;

INSERT INTO tmpRandomWord (id, cuv, surse)
SELECT L.id, L.formNoAccent cuv, GROUP_CONCAT(DISTINCT S.shortName) surse
FROM Lexem L
JOIN EntryDefinition ED ON L.entryId=ED.entryId
JOIN Definition D ON D.id=ED.definitionId
JOIN Source S ON D.sourceId=S.id
WHERE S.isOfficial=2
AND status=0
GROUP BY L.form;

RENAME TABLE RandomWord TO _tmp, tmpRandomWord TO RandomWord, _tmp TO tmpRandomWord;

DROP TABLE IF EXISTS tmpRandomWord;
EOQ
