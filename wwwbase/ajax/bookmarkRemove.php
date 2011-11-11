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
  log_userLog("Removed from favorites: {$bookmark->id} - the definition with the id {$bookmark->definitionId} of user: {$bookmark->userId}");
  $bookmark->delete();
}
$response['status'] = 'success';

echo json_encode($response);
?>
