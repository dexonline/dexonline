<?php

WordOfTheMonth::$IMAGE_DESCRIPTION_DIR = util_getRootPath() . 'docs/imageCredits';

class WordOfTheMonth extends BaseObject {
  public static $_table = 'WordOfTheMonth';
  public static $IMAGE_DESCRIPTION_DIR;

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
    if ($this->image) {
      return Config::get('static.url') . 'img/wotd/cuvantul-lunii/thumb/' . $this->image;
    }
    return null;
  }

  public function getImageCredits() {
    if (!$this->image) {
      return null;
    }
    $lines = @file(self::$IMAGE_DESCRIPTION_DIR . "/wotm.desc");
    if (!$lines) {
      return null;
    }
    foreach ($lines as $line) {
      $commentStart = strpos($line, '#');
      if ($commentStart !== false) {
        $line = substr($line, 0, $commentStart);
      }
      $line = trim($line);
      if ($line) {
        $parts = explode('::', trim($line));
        if (preg_match("/{$parts[0]}/", $this->image)) {
          $filename = self::$IMAGE_DESCRIPTION_DIR . '/' . $parts[1];
          return @file_get_contents($filename); // This could be false if the file does not exist.
        }
      }
    }
    return null;
  }

}

?>
