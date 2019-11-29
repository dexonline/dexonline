<?php

require_once '../../lib/Core.php';
User::mustHave(User::PRIV_ADMIN);

$action = Request::get('action');
$pageindexId = Request::get('pageindexId');
$sourceId = Request::get('sourceId');
$volume = Request::get('volume');
$page = Request::get('page');
$word = Request::get('word');
$number = Request::get('number');
$userId = User::getActiveId();
$status = 'hold';
$html = '';

if ($pageindexId) {
  $pageindex = PageIndex::get_by_id($pageindexId);
} else {
  $pageindex = Model::factory('PageIndex')->create();
  $pageindex->sourceId = $sourceId;
}

switch ($action) {
  case 'delete':
    $pageindex->delete();
    break;

  case 'duplicate':
    $html = 'Acest index de pagină există!';
    break;

  default:
    /** Populate the fields with new values and save */
    $pageindex->volume = $volume;
    $pageindex->page = $page;
    $pageindex->word = $word;
    $pageindex->number = $number;
    $pageindex->modUserId = $userId;
    $pageindex->save();

    /** Prepare the tableRow from template */
    Smart::assign('row', $pageindex);
    Smart::assign('labelEdited', 'primary');
    $html = Smart::fetch('bits/pageindexRow.tpl');
    $status = 'finished';
    break;

}

$response = [ 'id' => $pageindex->id,
              'action' => $action,
              'status' => $status,
              'html' => $html, ];

header('Content-Type: application/json');
print json_encode($response);
