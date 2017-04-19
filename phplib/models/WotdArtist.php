<?php

class WotdArtist extends BaseObject {
  public static $_table = 'WotdArtist';

  const CREDITS_FILE_WOTM = 'docs/imageCredits/wotm.desc';

  static function getAllWotmCredits() {
    $result = [];

    $lines = @file(Core::getRootPath() . self::CREDITS_FILE_WOTM);
    if ($lines) {
      foreach ($lines as $line) {
        $commentStart = strpos($line, '#');
        if ($commentStart !== false) {
          $line = substr($line, 0, $commentStart);
        }
        $line = trim($line);
        if ($line) {
          $parts = explode('::', trim($line));
          $result[] = [
            'regexp' => $parts[0],
            'label' => $parts[1],
          ];
        }
      }
    }

    return $result;
  }

  static function getByDate($displayDate, $wotm = false) {
    if ($wotm) {
      $credits = self::getAllWotmCredits();
      foreach ($credits as $line) {
        if (preg_match('/' . $line['regexp'] . '/', $displayDate)) {
          return WotdArtist::get_by_label($line['label']);
        }
      }
      return null;
    } else {
      $wa = WotdAssignment::get_by_date($displayDate);
      return $wa ? WotdArtist::get_by_id($wa->artistId) : null;
    }
  }

}
