<?

require_once("../phplib/util.php");

$definitionId = util_getRequestParameter('definitionId');

$userId = session_getUserId();
if (!$userId) {
  util_redirect('login');
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
} else {
  session_setFlash('Ați depășit limita de cuvinte favorite. Limita este ' . pref_getMaxBookmarks() . ' cuvinte favorite.');
}

$whereToGo = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/';
header("Location: {$whereToGo}");
?>
