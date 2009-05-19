<?
require_once("../../phplib/util.php"); 
ini_set("memory_limit", "128000000");
util_hideEmptyRequestParameters();
util_assertModeratorStatus();
util_assertNotMirror();

$sourceId = util_getRequestParameter('source');

if ($sourceId) {
  $source = Source::load($sourceId);
  RecentLink::createOrUpdate("Lexeme neetichetate {$source->shortName}");
  smarty_assign('sectionTitle', "Lexeme neetichetate {$source->shortName}");
  $lexems = Lexem::loadTemporaryFromSource($sourceId);
} else {
  RecentLink::createOrUpdate('Lexeme neetichetate');
  smarty_assign('sectionTitle', 'Lexeme neetichetate');
  $lexems = Lexem::loadTemporary();
}

smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexems', $lexems);
smarty_displayWithoutSkin('admin/lexemList.ihtml');

?>
