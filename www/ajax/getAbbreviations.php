<?php

require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$sourceId = Request::get('sourceId', 0);

if ($sourceId) {
  $abbrevs = Model::factory('Abbreviation')
    ->where('sourceId', $sourceId)
    ->order_by_asc('short')
    ->find_many();
}

Smart::assign([
  'sourceId' => $sourceId,
  'results'=> $abbrevs,
]);

$output = Smart::fetch('ajax/getAbbreviations.tpl');
$debug = Smart::fetch('bits/debugInfoAjax.tpl');

$results = [
  'count'=> count($abbrevs),
  'html'=> $output,
  'debug' => $debug,
];

header('Content-Type: application/json');
print json_encode($results);