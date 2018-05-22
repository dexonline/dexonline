<?php

class WordOfTheMonth extends BaseObject implements DatedObject {
  public static $_table = 'WordOfTheMonth';

  const DEFAULT_IMAGE = 'generic.jpg';
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

  // TODO: this duplicates code from WordOfTheDay.php
  function getThumbUrl($size) {
    $pic = $this->image ? $this->image : self::DEFAULT_IMAGE;
    return sprintf('%simg/wotd/thumb%s/cuvantul-lunii/%s',
                   Config::get('static.url'),  $size, $pic);
  }

  function getMediumThumbUrl() {
    return $this->getThumbUrl(WordOfTheDay::SIZE_M);
  }

  function getLargeThumbUrl() {
    return $this->getThumbUrl(WordOfTheDay::SIZE_L);
  }

  function getArtist() {
    return ($this->image)
      ? WotdArtist::getByDate($this->displayDate, true) // true = WotM
      : null;
  }

}

WordOfTheMonth::$IMAGE_CREDITS_DIR = Core::getRootPath() . 'docs/imageCredits';
