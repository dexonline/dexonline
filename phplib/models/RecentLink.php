<?php

class RecentLink extends BaseObject {
  public static function get($where) {
    $obj = new RecentLink();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }

  public static function createOrUpdate($text) {
    $userId = session_getUserId();
    $url = $_SERVER['REQUEST_URI'];
    $rl = self::get(sprintf("userId = %s and url = '%s' and text = '%s'", $userId, addslashes($url), addslashes($text)));

    if (!$rl) {
      $rl = new RecentLink();
      $rl->userId = $userId;
      $rl->url = $url;
      $rl->text = $text;
    }

    $rl->visitDate = time();
    $rl->save();
  }

  // Also deletes the ones in excess of MAX_RECENT_LINKS
  public static function loadForUser() {
    $userId = session_getUserId();
    $recentLinks = db_find(new RecentLink(), "userId = {$userId} order by visitDate desc");
    while (count($recentLinks) > MAX_RECENT_LINKS) {
      $deadLink = array_pop($recentLinks);
      $deadLink->delete();
    }
    return $recentLinks;
  }
}

?>
