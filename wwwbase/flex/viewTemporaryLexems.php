<?php
require_once('../../phplib/util.php');
ini_set('memory_limit', '512M');
util_hideEmptyRequestParameters();
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$sourceId = util_getRequestParameter('source');

if ($sourceId) {
  $source = Source::get_by_id($sourceId);
  RecentLink::createOrUpdate("Lexeme neetichetate {$source->shortName}");
  smarty_assign('sectionTitle', "Lexeme neetichetate {$source->shortName}");
  $lexems = Model::factory('Lexem')
    ->raw_query("select distinct L.* from Lexem L, LexemDefinitionMap, Definition D where L.id = lexemId and definitionId = D.id " .
                "and status = 0 and sourceId = {$sourceId} and modelType = 'T' order by formNoAccent", null)
    ->find_many();
} else {
  RecentLink::createOrUpdate('Lexeme neetichetate');
  smarty_assign('sectionTitle', 'Lexeme neetichetate');
  $lexems = Model::factory('Lexem')->where('modelType', 'T')->order_by_asc('formNoAccent')->find_many();
}

smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexems', $lexems);
smarty_assign('sectionCount', count($lexems));
smarty_displayAdminPage('admin/lexemList.ihtml');

?>
