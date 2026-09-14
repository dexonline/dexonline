<?php

class Proverb extends BaseObject implements DatedObject {
  public static $_table = 'Proverb';

  const DEFAULT_IMAGE = 'proverb-în-lucru.gif';
  const SIZE_M  =  88;
  const SIZE_L = 150;
  const SIZE_XL = 600;
  const SIZE_XXL=1080;

  static function getProverbsFromYear($year) {
    return Model::factory('Proverb')
      ->raw_query("SELECT id FROM Proverb WHERE YEAR(displayDate)=$year")
      ->find_many();
  }

  static function getProverb($id) {
    return Model::factory('Proverb')
      ->where('id', $id)
      ->find_one();
  }

  static function getTodayProverb() {
    return Model::factory('Proverb')
      ->raw_query('SELECT * FROM Proverb WHERE displayDate < NOW() order by displayDate DESC LIMIT 1')
      ->find_one();
    /*
    ->where_lte('displayDate', 'NOW()')
    ->order_by_desc('displayDate')
    ->limit(1)
    ->find_one();
    */

  }

  static function getProverbFromDate($date) {
    return Model::factory('Proverb')
      ->where_lte('displayDate', $date)
      ->order_by_desc('displayDate')
      ->limit(1)
      ->find_one();
  }

  static function getCurrentProverb() {
    $today = date('Y-m-d');
    return Model::factory('Proverb')
      ->where_lte('displayDate', $today)
      ->order_by_desc('displayDate')
      ->limit(1)
      ->find_one();
  }

  function getImageUrl() {
    if ($this->image) {
      return Config::STATIC_URL . 'img/proverbe/' . $this->image;
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
    return sprintf('%simg/proverbe/thumb%s/%s',
      Config::STATIC_URL,  $size, $pic);
  }

  function getDefaultThumbUrlSize($size) {
    return sprintf('%simg/proverbe/thumb%s/%s',Config::STATIC_URL, $size, self::DEFAULT_IMAGE);
  }

  function getMediumThumbUrl() {
    return $this->getThumbUrl(self::SIZE_M);
  }

  function getLargeThumbUrl() {
    return $this->getThumbUrl(self::SIZE_L);
  }

  function getXLargeThumbUrl() {
    return $this->getThumbUrl(self::SIZE_XL);
  }

  function getXXLargeThumbUrl() {
    return $this->getThumbUrl(self::SIZE_XXL);
  }

  function getDefaultThumbUrl() {
    //   return sprintf('%simg/proverbe/%s',Config::STATIC_URL, self::DEFAULT_IMAGE);
    return $this->getDefaultThumbUrlSize(self::SIZE_M);
  }

  function getXMediumDefaultThumbUrl() {
    //return $this->getDefaultThumbUrlSize(self::SIZE_XM);
  }

  function getArtist() {
    return ($this->image)
      ? WotdArtist::getByDate($this->displayDate, true) // true = WotM
      : null;
  }

}
