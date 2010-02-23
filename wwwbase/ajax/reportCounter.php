<?
require_once("../../phplib/util.php");
util_assertModeratorStatus();
util_assertNotMirror();

$reportId = util_getRequestParameter('report');
switch($reportId) {
  case 'unassociatedLexems': echo Lexem::countUnassociated(); break;
  case 'unassociatedDefinitions': echo Definition::countUnassociated(); break;
  case 'definitionsWithTypos': echo Definition::countHavingTypos(); break;
  case 'temporaryDefinitions': echo Definition::countByStatus(ST_PENDING); break;
  case 'temporaryLexems': echo Lexem::countTemporary(); break;
  case 'lexemsWithComments': echo Lexem::countHavingComments(); break;
  case 'lexemsWithoutAccents': echo Lexem::countWithoutAccents(); break;
  case 'ambiguousLexems': echo Lexem::countAmbiguous(); break;
  default: echo 'Necunoscut';
}

?>
