<?php

require_once '../../lib/Core.php';

$definitionId = Request::get('definitionId');

$response = [];
$userId = User::getActiveId();
if (!$userId) {
  $response['status'] = 'redirect';
  $response['url'] = 'login';

  echo json_encode($response);
  exit;
}

$bookmarks = UserWordBookmark::get_all_by_userId($userId);
if (count($bookmarks) < Config::LIMIT_BOOKMARKS) {
  $existing = Model::factory('UserWordBookmark')
            ->where('userId', $userId)
            ->where('definitionId', $definitionId)
            ->find_one();

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
  $response['msg'] = 'Puteți alege maximum ' . Config::LIMIT_BOOKMARKS .
                   ' de cuvinte favorite.';
}

echo json_encode($response);
