<?php

require_once("../../phplib/util.php");

$definitionId = util_getRequestParameter('definitionId');

$response = array();
$userId = session_getUserId();
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
?>
