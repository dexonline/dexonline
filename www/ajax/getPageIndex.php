<?php

require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$sourceId = Request::get('sourceId', 0);

if ($sourceId) {
  $pages = Model::factory('PageIndex')
    ->where('sourceId', $sourceId)
    ->order_by_asc([ 'volume', 'page', 'word', 'number'])
    ->find_many();
}

Smart::assign([
  'sourceId' => $sourceId,
  'results'=> $pages,
]);

$output = Smart::fetch('ajax/getPageIndex.tpl');
$debug = Smart::fetch('bits/debugInfoAjax.tpl');

$results = [
  'count'=> count($pages),
  'html'=> $output,
  'debug' => $debug,
];

header('Content-Type: application/json');
print json_encode($results);
