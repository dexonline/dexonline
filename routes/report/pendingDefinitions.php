<?php

User::mustHave(User::PRIV_EDIT);

$sourceId = 0;
$sourceUrlName = Request::get('source');
if ($sourceUrlName) {
  $source = $sourceUrlName ? Source::get_by_urlName($sourceUrlName) : null;
  $sourceId = $source ? $source->id : 0;
  Smart::assign('sourceId', $sourceId);
}

$ip = $_SERVER['REMOTE_ADDR'];

$defs = Model::factory('Definition')
  ->where('status', Definition::ST_PENDING);

if ($sourceId) {
  $defs = $defs->where('sourceId', $sourceId);
}

$pending_show_limit = 2 * Config::LIMIT_TRAINEE_PENDING_DEFS;
$defs = $defs
  ->order_by_asc('lexicon')
  ->order_by_asc('sourceId')
  ->limit($pending_show_limit)
  ->find_many();

$searchResults = SearchResult::mapDefinitionArray($defs);

Smart::assign('searchResults', $searchResults);
Smart::addResources('admin');
Smart::display('report/pendingDefinitions.tpl');
