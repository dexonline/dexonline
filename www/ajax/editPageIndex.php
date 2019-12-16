<?php

require_once '../../lib/Core.php';
User::mustHave(User::PRIV_ADMIN);

$action = Request::get('action');
$pageIndexId = Request::get('pageIndexId');
$sourceId = Request::get('sourceId');
$volume = Request::get('volume');
$page = Request::get('page');
$word = Request::get('word');
$number = Request::get('number');
$userId = User::getActiveId();
$status = 'hold';
$html = '';

if ($pageIndexId) {
  $pageIndex = PageIndex::get_by_id($pageIndexId);
} else {
  $pageIndex = Model::factory('PageIndex')->create();
  $pageIndex->sourceId = $sourceId;
}

switch ($action) {
  case 'delete':
    $status = 'finished';
    $pageIndex->delete();
    break;

  default:
    /** Populate the fields with new values and save */
    $pageIndex->volume = $volume;
    $pageIndex->page = $page;
    $pageIndex->word = $word;
    $pageIndex->number = $number;
    $pageIndex->modUserId = $userId;
    $pageIndex->save();

    /** Prepare the tableRow from template */
    Smart::assign('row', $pageIndex);
    Smart::assign('labelEdited', 'primary');
    $html = Smart::fetch('bits/pageIndexRow.tpl');
    $status = 'finished';
    break;

}

$response = [ 'id' => $pageIndex->id,
  'action' => $action,
  'status' => $status,
  'html' => $html, ];

header('Content-Type: application/json');
print json_encode($response);
