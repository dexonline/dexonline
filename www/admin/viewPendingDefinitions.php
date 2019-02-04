<?php
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$sourceId = 0;
$sourceUrlName = Request::get('source');
if ($sourceUrlName) {
  $source = $sourceUrlName ? Source::get_by_urlName($sourceUrlName) : null;
  $sourceId = $source ? $source->id : 0;
  SmartyWrap::assign('sourceId', $sourceId);
}

$ip = $_SERVER['REMOTE_ADDR'];

$defs = Model::factory('Definition')
  ->where('status', Definition::ST_PENDING);

if ($sourceId) {
  $defs = $defs->where('sourceId', $sourceId);
}

$defs = $defs
  ->order_by_asc('lexicon')
  ->order_by_asc('sourceId')
  ->limit(500)
  ->find_many();

$searchResults = SearchResult::mapDefinitionArray($defs);

SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewPendingDefinitions.tpl');
