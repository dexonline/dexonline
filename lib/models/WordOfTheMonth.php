<?php

class WordOfTheMonth extends BaseObject implements DatedObject {
  public static $_table = 'WordOfTheMonth';

  const DEFAULT_IMAGE = 'generic.jpg';

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
      return Config::STATIC_URL . 'img/wotd/cuvantul-lunii/' . $this->image;
    }
    return null;
  }

  // TODO: this duplicates code from WordOfTheDay.php
  function getThumbUrl($size) {
    $pic = $this->image ? $this->image : self::DEFAULT_IMAGE;
    StaticUtil::ensureThumb(
      "img/wotd/cuvantul-lunii/{$pic}",
      "img/wotd/thumb{$size}/cuvantul-lunii/{$pic}",
      $size);
    return sprintf('%simg/wotd/thumb%s/cuvantul-lunii/%s',
                   Config::STATIC_URL,  $size, $pic);
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
