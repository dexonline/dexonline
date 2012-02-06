#!/bin/bash

cat << 'EOQ' | mysql -vvv -uroot DEX
DROP TABLE IF EXISTS tmpRandomWord;

CREATE TABLE IF NOT EXISTS tmpRandomWord LIKE RandomWord;

INSERT INTO tmpRandomWord
SELECT L.id, L.formNoAccent cuv, GROUP_CONCAT(DISTINCT S.shortName) surse
FROM Lexem L
JOIN LexemDefinitionMap M ON L.id=M.lexemId
JOIN Definition D ON D.id=M.definitionId
JOIN Source S ON D.sourceId=S.id
WHERE S.isOfficial=2
AND status=0
GROUP BY L.form;

RENAME TABLE RandomWord TO _tmp, tmpRandomWord TO RandomWord, _tmp TO tmpRandomWord;
EOQ
