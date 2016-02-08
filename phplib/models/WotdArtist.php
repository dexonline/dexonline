<?php

class WotdArtist extends BaseObject {
  public static $_table = 'WotdArtist';

  const CREDITS_FILE_WOTM = 'docs/imageCredits/wotm.desc';

  static function getByDate($displayDate, $wotm = false) {
    if ($wotm) {
      $lines = @file(util_getRootPath() . self::CREDITS_FILE_WOTM);
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
    } else {
      $wa = WotdAssignment::get_by_date($displayDate);
      return $wa ? WotdArtist::get_by_id($wa->artistId) : null;
    }
  }

}
