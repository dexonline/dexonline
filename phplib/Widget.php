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
  const DATA = [
    self::WIDGET_WOTD => [
      'name' => 'Cuvântul zilei',
      'template' => 'wotd.tpl',
      'enabled' => true,
    ],
    self::WIDGET_WOTM => [
      'name' => 'Cuvântul lunii',
      'template' => 'wotm.tpl',
      'enabled' => true,
    ],
    self::WIDGET_RANDOM_WORD => [
      'name' => 'Cuvânt aleator',
      'template' => 'randomWord.tpl',
      'enabled' => true,
    ],
    self::WIDGET_AOTM => [
      'name' => 'Articolul lunii',
      'template' => 'articleOfTheMonth.tpl',
      'enabled' => true,
    ],
    self::WIDGET_GAMES => [
      'name' => 'Jocuri',
      'template' => 'games.tpl',
      'enabled' => true,
    ],
    self::WIDGET_SOCIAL => [
      'name' => 'Rețele sociale',
      'template' => 'social.tpl',
      'enabled' => true,
    ],
  ];

  /**
   * Returns a copy of DATA with the 'enabled' field modified where necessary.
   * widgetCount stores the number of widgets when the user last saved the widgetMask.
   * For new widgets between $widgetCount + 1 and WIDGET_COUNT - 1, which have been added since the last save,
   * we use the widget's default state.
   **/
  static function getWidgets($widgetMask, $widgetCount) {
    $result = self::DATA;
    for ($mask = 1; $mask < 1 << $widgetCount; $mask <<= 1) {
      $result[$mask]['enabled'] = ($widgetMask & $mask) ? true : false;
    }
    return $result;
  }

  static function getDefaultWidgetMask() {
    $result = 0;
    foreach (self::DATA as $mask => $params) {
      if ($params['enabled']) {
        $result += $mask;
      }
    }
    return $result;
  }
}
