<?php

class Widget {
  const WIDGET_WOTD = 0x01;
  const WIDGET_WOTM = 0x02;
  const WIDGET_RANDOM_WORD = 0x04;
  const WIDGET_AOTM = 0x08;
  const WIDGET_GAMES = 0x10;
  const WIDGET_SOCIAL = 0x20;
  const WIDGET_COUNT = 6;

  public static $WIDGET_NAMES = array(self::WIDGET_WOTD => 'Cuvântul zilei',
                                      self::WIDGET_WOTM => 'Cuvântul lunii',
                                      self::WIDGET_RANDOM_WORD => 'Cuvânt aleator',
                                      self::WIDGET_AOTM => 'Articolul lunii',
                                      self::WIDGET_GAMES => 'Jocuri',
                                      self::WIDGET_SOCIAL => 'Rețele sociale'
                                      );

  public static $WIDGET_TEMPLATES = array(self::WIDGET_WOTD => 'wotd.ihtml',
                                          self::WIDGET_WOTM => 'wotm.ihtml',
                                          self::WIDGET_RANDOM_WORD => 'randomWord.ihtml',
                                          self::WIDGET_AOTM => 'articleOfTheMonth.ihtml',
                                          self::WIDGET_GAMES => 'games.ihtml',
                                          self::WIDGET_SOCIAL => 'social.ihtml'
                                          );

  static function getWidgets($mask, $widgetCount) {
    $result = array();
    foreach (self::$WIDGET_TEMPLATES as $widgetMask => $template) {
      if ($mask & $widgetMask) {
        $result[$widgetMask] = $template;
      }
    }
    return $result;
  }
}

?>
