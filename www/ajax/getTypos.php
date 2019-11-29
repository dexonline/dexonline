<?php
/**
 * Sends definitions with typos
 **/
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$sourceId = Request::get('sourceId');
$defs = Definition::loadTypos($sourceId);

Smart::assign( [
  'searchResults' => SearchResult::mapDefinitionArray($defs),
]);

$output = Smart::fetch('report/typosList.tpl');
$debug = Smart::fetch('bits/debugInfoAjax.tpl');

$results = [
  'count'=> count($defs),
  'html'=> $output,
  'debug' => $debug,
];

header('Content-Type: application/json');
print json_encode($results);
