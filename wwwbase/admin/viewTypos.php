<?php
require_once '../../phplib/Core.php';
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$sourceClause = '';
$sourceUrlName = Request::get('source');
if ($sourceUrlName) {
  $source = $sourceUrlName ? Source::get_by_urlName($sourceUrlName) : null;
  $sourceId = $source ? $source->id : 0;
  $sourceClause = $source ? "sourceId = {$sourceId} and " : '';
  SmartyWrap::assign('sourceId', $sourceId);
}

$defs = Model::factory('Definition')
  ->raw_query("select * from Definition where {$sourceClause} id in (select definitionId from Typo) order by lexicon")->find_many();

SmartyWrap::assign('searchResults', SearchResult::mapDefinitionArray($defs));
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewTypos.tpl');
