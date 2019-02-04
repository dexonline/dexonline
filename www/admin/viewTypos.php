<?php
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$sourceClause = '';
$sourceUrlName = Request::get('source');
if ($sourceUrlName) {
  $source = $sourceUrlName ? Source::get_by_urlName($sourceUrlName) : null;
  $sourceId = $source ? $source->id : 0;
  $sourceClause = $source ? "sourceId = {$sourceId} and " : '';
  Smart::assign('sourceId', $sourceId);
}

$defs = Model::factory('Definition')
  ->raw_query("select * from Definition where {$sourceClause} id in (select definitionId from Typo) order by lexicon")->find_many();

Smart::assign('searchResults', SearchResult::mapDefinitionArray($defs));
Smart::addCss('admin');
Smart::display('admin/viewTypos.tpl');
