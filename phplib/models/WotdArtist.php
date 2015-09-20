<?php

class WotdArtist extends BaseObject implements DatedObject {
  public static $_table = 'WotdArtist';

  const CREDITS_FILE_WOTD = 'docs/imageCredits/wotd.desc';
  const CREDITS_FILE_WOTM = 'docs/imageCredits/wotm.desc';

  static function getByDate($displayDate, $wotm = false) {
    $creditsFile = $wotm ? self::CREDITS_FILE_WOTM : self::CREDITS_FILE_WOTD;
    $lines = @file(util_getRootPath() . $creditsFile);
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
        if (preg_match("/{$parts[0]}/", $displayDate)) {
          return WotdArtist::get_by_label($parts[1]);
        }
      }
    }
    return null;
  }

}
