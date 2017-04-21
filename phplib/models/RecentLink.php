<?php

class RecentLink extends BaseObject {
  public static $_table = 'RecentLink';

  const MAX_RECENT_LINKS = 20;

  static function add($text) {
    $userId = Session::getUserId();
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
  static function load() {
    $userId = Session::getUserId();
    $recentLinks = Model::factory('RecentLink')
                 ->where('userId', $userId)
                 ->order_by_desc('visitDate')
                 ->find_many();
    while (count($recentLinks) > self::MAX_RECENT_LINKS) {
      $deadLink = array_pop($recentLinks);
      $deadLink->delete();
    }
    return $recentLinks;
  }
}

?>
