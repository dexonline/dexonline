<?php

class Widget {
  const WIDGET_WOTD = 0x01;
  const WIDGET_WOTM = 0x02;
  const WIDGET_RANDOM_WORD = 0x04;
  const WIDGET_AOTM = 0x08;
  const WIDGET_GAMES = 0x10;
  const WIDGET_SOCIAL = 0x20;
  const WIDGET_COUNT = 6;

  // 'enabled' means "enabled by default". All widgets can later be enabled or disabled based on user prefs.
  public static $DATA = array(self::WIDGET_WOTD        => array('name' => 'Cuvântul zilei',
                                                                'template' => 'wotd.ihtml',
                                                                'enabled' => true),
                              self::WIDGET_WOTM        => array('name' => 'Cuvântul lunii',
                                                                'template' => 'wotm.ihtml',
                                                                'enabled' => true),
                              self::WIDGET_RANDOM_WORD => array('name' => 'Cuvânt aleator',
                                                                'template' => 'randomWord.ihtml',
                                                                'enabled' => true),
                              self::WIDGET_AOTM        => array('name' => 'Articolul lunii',
                                                                'template' => 'articleOfTheMonth.ihtml',
                                                                'enabled' => true),
                              self::WIDGET_GAMES       => array('name' => 'Jocuri',
                                                                'template' => 'games.ihtml',
                                                                'enabled' => true),
                              self::WIDGET_SOCIAL      => array('name' => 'Rețele sociale',
                                                                'template' => 'social.ihtml',
                                                                'enabled' => true),
                              );

  /**
   * Returns a copy of DATA with the 'enabled' field modified where necessary.
   * widgetCount stores the number of widgets when the user last saved the widgetMask.
   * For new widgets between $widgetCount + 1 and WIDGET_COUNT - 1, which have been added since the last save,
   * we use the widget's default state.
   **/
  static function getWidgets($widgetMask, $widgetCount) {
    $result = self::$DATA;
    for ($mask = 1; $mask < 1 << $widgetCount; $mask <<= 1) {
      $result[$mask]['enabled'] = ($widgetMask & $mask) ? true : false;
    }
    return $result;
  }

  static function getDefaultWidgetMask() {
    $result = 0;
    foreach (self::$DATA as $mask => $params) {
      if ($params['enabled']) {
        $result += $mask;
      }
    }
    return $result;    
  }
}

?>
