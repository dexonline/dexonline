<?php

class RecentLink extends BaseObject {
  public static $_table = 'RecentLink';

  public static function add($text) {
    $userId = session_getUserId();
    $url = $_SERVER['REQUEST_URI'];
    $rl = Model::factory('RecentLink')
        ->where('userId', $userId)
        ->where('url', $url)
        ->where('text', $text)
        ->find_one();

    if (!$rl) {
      $rl = Model::factory('RecentLink')->create();
      $rl->userId = $userId;
      $rl->url = $url;
      $rl->text = $text;
    }

    $rl->visitDate = time();
    $rl->save();
  }

  // Also deletes the ones in excess of MAX_RECENT_LINKS
  public static function load() {
    $userId = session_getUserId();
    $recentLinks = Model::factory('RecentLink')
                 ->where('userId', $userId)
                 ->order_by_desc('visitDate')
                 ->find_many();
    while (count($recentLinks) > MAX_RECENT_LINKS) {
      $deadLink = array_pop($recentLinks);
      $deadLink->delete();
    }
    return $recentLinks;
  }
}

?>
