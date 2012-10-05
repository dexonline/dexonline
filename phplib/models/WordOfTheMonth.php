<?php

WordOfTheMonth::$IMAGE_DIR = util_getRootPath() . "wwwbase/img/wotd/cuvantul-lunii";
WordOfTheMonth::$THUMB_DIR = util_getRootPath() . "wwwbase/img/wotd/thumb/cuvantul-lunii";
WordOfTheMonth::$IMAGE_DESCRIPTION_DIR = util_getRootPath() . "wwwbase/img/wotd/desc";
WordOfTheMonth::$THUMB_SIZE = 48;

class WordOfTheMonth extends BaseObject {
  public static $_table = 'WordOfTheMonth';
  public static $IMAGE_DIR;
  public static $THUMB_DIR;
  public static $IMAGE_DESCRIPTION_DIR;
  public static $THUMB_SIZE;

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
    if ($this->image && file_exists(self::$IMAGE_DIR . "/{$this->image}")) {
      return "wotd/cuvantul-lunii/{$this->image}"; // Relative to the image path
    }
    return null;
  }

  public function getImageCredits() {
    if (!$this->image) {
      return null;
    }
    $lines = @file(self::$IMAGE_DESCRIPTION_DIR . "/authors.desc");
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

  public function getThumbUrl() {
    if ($this->image && file_exists(self::$THUMB_DIR . "/{$this->image}")) {
      return "wotd/cuvantul-lunii/thumb/{$this->image}"; // Relative to the image path
    }
    return null;
  }

  public function ensureThumbnail() {
    if (!$this->image) {
      return;
    }
    $fullImage = self::$IMAGE_DIR . "/{$this->image}";
    $fullThumb = self::$THUMB_DIR . "/{$this->image}";
    if (!file_exists($fullThumb) && file_exists($fullImage)) {
      $oldumask = umask(0);
      @mkdir(dirname($fullThumb), 0777, true);
      umask($oldumask);
      OS::executeAndAssert(sprintf("convert -strip -geometry %dx%d -sharpen 1x1 '%s' '%s'",
                                   self::$THUMB_SIZE, self::$THUMB_SIZE, $fullImage, $fullThumb));
    }
  }
}

?>
