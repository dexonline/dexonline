<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$reportId = util_getRequestParameter('report');
switch($reportId) {
 case 'unassociatedLexems': echo count(Lexem::loadUnassociated()); break;
  case 'unassociatedDefinitions': echo Definition::countUnassociated(); break;
  case 'definitionsWithTypos': echo db_getSingleValue('select count(distinct definitionId) from Typo'); break;
  case 'temporaryDefinitions': echo Definition::countByStatus(ST_PENDING); break;
  case 'temporaryLexems': echo db_getSingleValue("select count(*) from Lexem where modelType = 'T'"); break;
  case 'lexemsWithComments': echo db_getSingleValue("select count(*) from Lexem where comment != ''"); break;
  case 'lexemsWithoutAccents': echo db_getSingleValue("select count(*) from Lexem where form not rlike '\'' and not noAccent"); break;
  case 'wotd': echo db_getSingleValue("select count(*) from WordOfTheDay"); break;
  case 'definitionsWithAmbiguousAbbrev':
    echo db_getSingleValue("select count(*) from Definition where status != " . ST_DELETED . " and abbrevReview = " . ABBREV_AMBIGUOUS); break;
  case 'ambiguousLexems': // This one is expensive
    echo db_getSingleValue("select count(*) from (select id from Lexem where description = '' group by form having count(*) > 1) as t1");
    break;
  default: echo 'Necunoscut';
}

?>
