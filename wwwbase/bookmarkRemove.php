<?

require_once("../phplib/util.php");

$definitionId = util_getRequestParameter('definitionId');

$userId = session_getUserId();
if (!$userId) {
  util_redirect('login');
}

$bookmarkList = UserWordBookmark::loadByUserIdAndDefinitionId($userId, $definitionId);
if ($bookmarkList) {
  foreach ($bookmarkList as $bookmark) { /* Should be at most one */
    if (!is_null($bookmark)) {
      log_userLog("Removed from favorites: {$bookmark->id} - the definition with the id {$bookmark->definitionId} of user: {$bookmark->userId}");
      $bookmark->delete();
    }
  }
}

$whereToGo = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/';
header("Location: {$whereToGo}");
?>
