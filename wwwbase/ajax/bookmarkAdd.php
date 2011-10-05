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

$bookmarks = UserWordBookmarkDisplayObject::getByUser($userId);
if (count($bookmarks) < pref_getMaxBookmarks()) {
  $bookmark = new UserWordBookmark();
  $status = $bookmark->getStatus($userId, $definitionId);
  
  if (is_null($status)) {
    $bookmark->userId = $userId; 
    $bookmark->definitionId = $definitionId;
    $bookmark->save();
    log_userLog("Added to favorites: {$bookmark->id} - the definition with the id {$bookmark->definitionId} for user {$bookmark->userId}");
  }

  $response['status'] = 'success';
} else {
  $response['status'] = 'error';
  $response['msg'] = 'Ați depășit limita de cuvinte favorite. Limita este ' . pref_getMaxBookmarks() . ' cuvinte favorite.';
}

echo json_encode($response);
?>
