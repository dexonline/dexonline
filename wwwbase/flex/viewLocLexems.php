<?
require_once("../../phplib/util.php"); 
ini_set("memory_limit", "256000000");
util_hideEmptyRequestParameters();
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$sourceId = util_getRequestParameter('source');
$loc = util_getRequestParameter('loc');

$source = Source::get("id={$sourceId}");
$query = "select l.* from Lexem l, LexemDefinitionMap ldm, Definition d where l.id = ldm.lexemId and ldm.definitionId = d.id and d.status = 0 and d.sourceId = $sourceId";
switch ($loc) {
  case 0:
    $query .= " and not l.isLoc";
    smarty_assign('sectionTitle', "Lexeme din {$source->shortName} neincluse în LOC");
    break;
  case 1:
    $query .= " and l.isLoc";
    smarty_assign('sectionTitle', "Lexeme din {$source->shortName} incluse în LOC");
    break;
  case 2:
    smarty_assign('sectionTitle', "Lexeme din {$source->shortName}");
    break;
  default: exit;
}

$dbResult = db_execute($query);
$lexems = db_getObjects(new Lexem(), $dbResult);

smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexems', $lexems);
smarty_displayWithoutSkin('admin/lexemList.ihtml');

?>
