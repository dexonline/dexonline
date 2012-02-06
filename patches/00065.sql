CREATE TABLE `RandomWord` (
    `id` int(11) NOT NULL DEFAULT '0',
    `cuv` char(50) NOT NULL,
    `surse` char(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


INSERT INTO RandomWord
SELECT L.id, L.formNoAccent cuv, GROUP_CONCAT(DISTINCT S.shortName) surse
FROM Lexem L
JOIN LexemDefinitionMap M ON L.id=M.lexemId
JOIN Definition D ON D.id=M.definitionId
JOIN Source S ON D.sourceId=S.id
WHERE S.isOfficial=2
AND status=0
GROUP BY L.form;
