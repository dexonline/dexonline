<?php
require_once('../../phplib/util.php');
ini_set('memory_limit', '512M');
util_hideEmptyRequestParameters();
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$sourceId = util_getRequestParameter('source');

if ($sourceId) {
  $source = Source::get_by_id($sourceId);
  $lexems = Model::factory('Lexem')
    ->table_alias('l')
    ->select('l.*')
    ->distinct()
    ->join('EntryDefinition', 'ed.entryId = l.entryId', 'ed')
    ->join('Definition', 'd.id = ed.definitionId', 'd')
    ->where('d.status', Definition::ST_ACTIVE)
    ->where('d.sourceId', $sourceId)
    ->where('l.modelType', 'T')
    ->order_by_asc('formNoAccent')
    ->limit(1000)
    ->find_many();
} else {
  $lexems = Model::factory('Lexem')
    ->where('modelType', 'T')
    ->order_by_asc('formNoAccent')
    ->limit(1000)
    ->find_many();
}

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewTemporaryLexems.tpl');

?>
