<?php

class ExpressionOfTheMonth extends BaseObject implements DatedObject {
  public static $_table = 'ExpressionOfTheMonth';

  const DEFAULT_IMAGE = 'generic.jpg';
  const SIZE_XM = 150;
  const SIZE_XL = 600;
  const SIZE_XXL=1080;

  static function getExpressionsFromYear($year) {
    return Model::factory('ExpressionOfTheMonth')
      ->raw_query("SELECT id FROM ExpressionOfTheMonth WHERE YEAR(displayDate)=$year")
      ->find_many();
  }

  static function getExpression($id) {
    return Model::factory('ExpressionOfTheMonth')
      ->where('id', $id)
      ->find_one();
  }

  static function getTodayExpression() {
    return Model::factory('ExpressionOfTheMonth')
      ->raw_query('SELECT * FROM ExpressionOfTheMonth WHERE displayDate < NOW() order by displayDate DESC LIMIT 1')
      ->find_one();
      /*
      ->where_lte('displayDate', 'NOW()')
      ->order_by_desc('displayDate')
      ->limit(1)
      ->find_one();
      */

  }

  static function getWotM($date) {
    return Model::factory('ExpressionOfTheMonth')
      ->where_lte('displayDate', $date)
      ->order_by_desc('displayDate')
      ->limit(1)
      ->find_one();
  }

  static function getCurrentWotM() {
    $today = date('Y-m-d');
    return Model::factory('ExpressionOfTheMonth')
      ->where_lte('displayDate', $today)
      ->order_by_desc('displayDate')
      ->limit(1)
      ->find_one();
  }

  function getImageUrl() {
    if ($this->image) {
      return Config::STATIC_URL . 'img/expresii/' . $this->image;
    }
    return null;
  }

  // TODO: this duplicates code from WordOfTheDay.php
  function getThumbUrl($size) {
    $pic = $this->image ? $this->image : self::DEFAULT_IMAGE;
    /* TODO: check why this is not working!
    StaticUtil::ensureThumb(
      "img/expresii/{$pic}",
      "img/expresii/thumb{$size}/{$pic}",
      $size);
    */
    return sprintf('%simg/expresii/thumb%s/%s',
      Config::STATIC_URL,  $size, $pic);
  }

  function getMediumThumbUrl() {
    return $this->getThumbUrl(WordOfTheDay::SIZE_M);
  }

  function getXMediumThumbUrl() {
    return $this->getThumbUrl(self::SIZE_XM);
  }

  function getLargeThumbUrl() {
    return $this->getThumbUrl(WordOfTheDay::SIZE_L);
  }

  function getXLargeThumbUrl() {
    return $this->getThumbUrl(self::SIZE_XL);
  }

  function getXXLargeThumbUrl() {
    return $this->getThumbUrl(self::SIZE_XXL);
  }

  function getArtist() {
    return ($this->image)
      ? WotdArtist::getByDate($this->displayDate, true) // true = WotM
      : null;
  }

}
