<?php

WordOfTheMonth::$IMAGE_CREDITS_DIR = util_getRootPath() . 'docs/imageCredits';

class WordOfTheMonth extends BaseObject {
  public static $_table = 'WordOfTheMonth';
  public static $DEFAULT_IMAGE = 'generic.jpg';
  public static $IMAGE_CREDITS_DIR;

  public static function getWotM($date) {
    return Model::factory('WordOfTheMonth')
      ->where_lte('displayDate', $date)
      ->order_by_desc('displayDate')
      ->limit(1)
      ->find_one();
  }

  public static function getCurrentWotM() {
    return Model::factory('WordOfTheMonth')
      ->where_raw('displayDate <= curdate()')
      ->order_by_desc('displayDate')
      ->limit(1)
      ->find_one();
  }

  public function getImageUrl() {
    if ($this->image) {
      return Config::get('static.url') . 'img/wotd/cuvantul-lunii/' . $this->image;
    }
    return null;
  }

  public function getThumbUrl() {
    $pic = $this->image ? $this->image : self::$DEFAULT_IMAGE;
    return Config::get('static.url') . 'img/wotd/thumb/cuvantul-lunii/' . $pic;
  }

  public function getArtist() {
    return ($this->image)
      ? WotdArtist::getByDate($this->displayDate, true) // true = WotM
      : null;
  }

}

?>
