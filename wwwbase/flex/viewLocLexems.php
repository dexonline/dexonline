<?
require_once("../../phplib/util.php"); 
util_hideEmptyRequestParameters();
util_assertModerator(PRIV_LOC);
util_assertNotMirror();

$sourceId = util_getRequestParameter('source');
$loc = util_getRequestParameter('loc');

$source = Source::get("id={$sourceId}");
switch ($loc) {
  case 0:
    $clause = " and not l.isLoc";
    smarty_assign('sectionTitle', "Lexeme din {$source->shortName} neincluse în LOC");
    break;
  case 1:
    $clause = " and l.isLoc";
    smarty_assign('sectionTitle', "Lexeme din {$source->shortName} incluse în LOC");
    break;
  case 2:
    $clause = "";
    smarty_assign('sectionTitle', "Lexeme din {$source->shortName}");
    break;
  default: exit;
}
$query = "select l.* from Lexem l, LexemDefinitionMap ldm, Definition d where l.id = ldm.lexemId and ldm.definitionId = d.id and d.status = 0 and d.sourceId = $sourceId $clause order by l.formNoAccent";
$lexemDbResult = db_execute($query);

smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexemDbResult', $lexemDbResult);
smarty_displayWithoutSkin('admin/lexemListDbResult.ihtml');

?>
