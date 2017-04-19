<?php

class WordOfTheDay extends BaseObject {
  public static $_table = 'WordOfTheDay';
  public static $DEFAULT_IMAGE;
  public static $IMAGE_CREDITS_DIR;

  // Thumbnail sizes
  const SIZE_S = 48;
  const SIZE_M = 88;

  public static function init() {
    self::$DEFAULT_IMAGE = "generic.jpg";
    self::$IMAGE_CREDITS_DIR = util_getRootPath() . 'docs/imageCredits';
  }

  public static function getRSSWotD($delay = 0) {
    $nowDate = ( $delay == 0 ) ? 'NOW()' : 'DATE_SUB(NOW(), INTERVAL ' . $delay. ' MINUTE)';
    return Model::factory('WordOfTheDay')->where_gt('displayDate', '2011-01-01')->where_raw('displayDate < ' . $nowDate)
      ->order_by_desc('displayDate')->limit(25)->find_many();
  }

  public static function getTodaysWord() {
    return Model::factory('WordOfTheDay')->where_raw('displayDate = curdate()')->find_one();
  }

  public static function updateTodaysWord() {
    DB::execute('update WordOfTheDay set displayDate=curdate() where displayDate is null order by priority, rand() limit 1');
  }

  public static function getPreviousYearsWotds($month, $day) {
    return Model::factory('WordOfTheDay')
      ->where_raw('month(displayDate) = ?', $month)
      ->where_raw('day(displayDate) = ?', $day)
      ->order_by_desc('displayDate')
      ->limit(25)
      ->find_many();
  }

  public static function getStatus($refId, $refType = 'Definition') {
    $result = Model::factory('WordOfTheDay')->table_alias('W')->select('W.id')->join('WordOfTheDayRel', 'W.id = R.wotdId', 'R')
      ->where('R.refId', $refId)->where('R.refType', $refType)->find_one();
    return $result ? $result->id : NULL;
  }

  public function getImageUrl() {
    $pic = $this->image ? $this->image : self::$DEFAULT_IMAGE;
    return Config::get('static.url') . 'img/wotd/' . $pic;
  }

  public function getThumbUrl($size) {
    $pic = $this->image ? $this->image : self::$DEFAULT_IMAGE;
    return sprintf('%simg/wotd/thumb%s/%s',
                   Config::get('static.url'),  $size, $pic);
  }

  public function getSmallThumbUrl() {
    return $this->getThumbUrl(self::SIZE_S);
  }

  public function getMediumThumbUrl() {
    return $this->getThumbUrl(self::SIZE_M);
  }

  public function getArtist() {
    return ($this->image)
      ? WotdArtist::getByDate($this->displayDate)
      : null;
  }

  // Expensive -- this fetches the URL from the static server
  public function imageExists() {
    if (!$this->image) {
      return true; // Not the case since there is no image
    }
    list($ignored, $httpCode) = util_fetchUrl($this->getImageUrl());
    return $httpCode == 200;
  }
}

WordOfTheDay::init();

?>
