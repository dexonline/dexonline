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
if (count($bookmarks) < Config::get('global.maxBookmarks')) {
  $existing = Model::factory('UserWordBookmark')->where('userId', $userId)->where('definitionId', $definitionId)->find_one();
  
  if (!$existing) {
    $bookmark = Model::factory('UserWordBookmark')->create();
    $bookmark->userId = $userId; 
    $bookmark->definitionId = $definitionId;
    $bookmark->save();
    Log::info("added {$bookmark->id}, definition ID = {$definitionId}");
  }

  $response['status'] = 'success';
} else {
  $response['status'] = 'error';
  $response['msg'] = 'Ați depășit limita de cuvinte favorite. Limita este ' . Config::get('global.maxBookmarks') . ' cuvinte favorite.';
}

echo json_encode($response);
?>
