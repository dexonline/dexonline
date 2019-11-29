<?php
/**
 * Sends definitions with missing rare glyphs tag
 **/
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$sourceId = Request::get('sourceId');
$defs = Definition::loadMissingRareGlyphsTags($sourceId);

Smart::assign( [
  'searchResults' => SearchResult::mapDefinitionArray($defs),
]);

$output = Smart::fetch('bits/definitionList.tpl');
$debug = Smart::fetch('bits/debugInfoAjax.tpl');

$results = [
  'count'=> count($defs),
  'html'=> $output,
  'debug' => $debug,
];

header('Content-Type: application/json');
print json_encode($results);
