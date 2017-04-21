<?php

class WordOfTheMonth extends BaseObject {
  public static $_table = 'WordOfTheMonth';
  public static $DEFAULT_IMAGE = 'generic.jpg';
  public static $IMAGE_CREDITS_DIR;

  static function getWotM($date) {
    return Model::factory('WordOfTheMonth')
      ->where_lte('displayDate', $date)
      ->order_by_desc('displayDate')
      ->limit(1)
      ->find_one();
  }

  static function getCurrentWotM() {
    return Model::factory('WordOfTheMonth')
      ->where_raw('displayDate <= curdate()')
      ->order_by_desc('displayDate')
      ->limit(1)
      ->find_one();
  }

  function getImageUrl() {
    if ($this->image) {
      return Config::get('static.url') . 'img/wotd/cuvantul-lunii/' . $this->image;
    }
    return null;
  }

  function getThumbUrl() {
    $pic = $this->image ? $this->image : self::$DEFAULT_IMAGE;
    // WotM only uses medium thumbs
    return Config::get('static.url') . 'img/wotd/thumb88/cuvantul-lunii/' . $pic;
  }

  function getArtist() {
    return ($this->image)
      ? WotdArtist::getByDate($this->displayDate, true) // true = WotM
      : null;
  }

}

WordOfTheMonth::$IMAGE_CREDITS_DIR = Core::getRootPath() . 'docs/imageCredits';

?>
