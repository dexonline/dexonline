<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$reportId = util_getRequestParameter('report');
switch($reportId) {
case 'unassociatedLexems': echo count(Lexem::loadUnassociated()); break;
case 'unassociatedDefinitions': echo Definition::countUnassociated(); break;
case 'definitionsWithTypos': echo Model::factory('Typo')->select('definitionId')->distinct()->count(); break;
case 'temporaryDefinitions': echo Definition::countByStatus(ST_PENDING); break;
case 'temporaryLexems': echo Model::factory('Lexem')->where('modelType', 'T')->count(); break;
case 'lexemsWithComments': echo Model::factory('Lexem')->where_not_equal('comment', '')->count(); break;
case 'lexemsWithoutAccents': echo Model::factory('Lexem')->where_raw("form not rlike '\''")->where('noAccent', false)->count(); break;
case 'wotd': echo Model::factory('WordOfTheDay')->count(); break;
case 'definitionsWithAmbiguousAbbrev':
  echo Model::factory('Definition')->where_not_equal('status', ST_DELETED)->where('abbrevReview', ABBREV_AMBIGUOUS)->count(); break;
case 'ambiguousLexems': // This one is expensive
  $r = Model::factory('Lexem')
    ->raw_query("select count(*) as c from (select id from Lexem where description = '' group by form having count(*) > 1) as t1", null)
    ->find_one();
  print $r->c;
  break;
case 'visualTag': echo Model::factory('Visual')->where('revised', 0)->count(); break;
default: echo 'Necunoscut';
}

?>
