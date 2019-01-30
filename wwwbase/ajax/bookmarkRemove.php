<?php

require_once '../../phplib/Core.php';

$definitionId = Request::get('definitionId');

$response = [];
$userId = User::getActiveId();
if (!$userId) {
  $response['status'] = 'redirect';
  $response['url'] = 'login';

  echo json_encode($response);
  exit();
}

$bookmark = Model::factory('UserWordBookmark')->where('userId', $userId)->where('definitionId', $definitionId)->find_one();
if ($bookmark) {
  Log::info("removed {$bookmark->id}, definition ID = {$definitionId}");
  $bookmark->delete();
}
$response['status'] = 'success';

echo json_encode($response);
