<?
require_once("../../phplib/util.php"); 
ini_set("memory_limit", "256000000");
util_hideEmptyRequestParameters();
util_assertModeratorStatus();
util_assertNotMirror();

$sourceId = util_getRequestParameter('source');

if ($sourceId) {
  $source = Source::get("id={$sourceId}");
  RecentLink::createOrUpdate("Lexeme neetichetate {$source->shortName}");
  smarty_assign('sectionTitle', "Lexeme neetichetate {$source->shortName}");
  $dbResult = db_execute("select distinct L.* from Lexem L, LexemDefinitionMap, Definition D where L.id = lexemId and definitionId = D.id " .
                         "and status = 0 and sourceId = {$sourceId} and modelType = 'T' order by formNoAccent");
  $lexems = db_getObjects(new Lexem(), $dbResult);
} else {
  RecentLink::createOrUpdate('Lexeme neetichetate');
  smarty_assign('sectionTitle', 'Lexeme neetichetate');
  $lexems = db_find(new Lexem(), "modelType = 'T' order by formNoAccent");
}

smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexems', $lexems);
smarty_displayWithoutSkin('admin/lexemList.ihtml');

?>
