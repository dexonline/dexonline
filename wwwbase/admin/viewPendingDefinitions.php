<?php
require_once("../../phplib/util.php"); 
User::require(User::PRIV_EDIT);
util_assertNotMirror();

$sourceId = 0;
$sourceUrlName = Request::get('source');
if ($sourceUrlName) {
  $source = $sourceUrlName ? Source::get_by_urlName($sourceUrlName) : null;
  $sourceId = $source ? $source->id : 0;
  SmartyWrap::assign('sourceId', $sourceId);
}

$ip = $_SERVER['REMOTE_ADDR'];

$defs = Model::factory('Definition')
      ->table_alias('d')
      ->select('d.*')
      ->join('EntryDefinition', ['ed.definitionId', '=', 'd.id'], 'ed')
      ->where('d.status', Definition::ST_PENDING);

if ($sourceId) {
  $defs = $defs->where('d.sourceId', $sourceId);
}

$defs = $defs
  ->order_by_asc('lexicon')
  ->order_by_asc('sourceId')
  ->limit(500)
  ->find_many();

$searchResults = SearchResult::mapDefinitionArray($defs);
FileCache::putModeratorQueryResults($ip, $searchResults);

SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewPendingDefinitions.tpl');
?>
