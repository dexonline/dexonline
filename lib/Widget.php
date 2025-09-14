<?php

class Widget {
  const WIDGET_WOTD = 0x01;
  const WIDGET_WOTM = 0x02;
  const WIDGET_RANDOM_WORD = 0x04;
  const WIDGET_AOTM = 0x08;
  const WIDGET_GAMES = 0x10;
  const WIDGET_SOCIAL = 0x20;
  const WIDGET_EXPRESSION = 0x40;
  const WIDGET_NEWSLETTER = 0x80;
  const WIDGET_PROVERB = 0x100;
  const WIDGET_COUNT = 9;

  // 'enabled' means "enabled by default". All widgets can later be enabled or disabled based on user prefs.
  // also this array determines the order of the widgets
  public static function getData() {
    return [
      self::WIDGET_WOTD => [
        'name' => _('word of the day'),
        'template' => 'wotd.tpl',
        'enabled' => true,
      ],
      self::WIDGET_WOTM => [
        'name' => _('word of the month'),
        'template' => 'wotm.tpl',
        'enabled' => true,
      ],
      self::WIDGET_EXPRESSION => [
        'name' => _('expressions'),
        'template' => 'expression.tpl',
        'enabled' => false,
      ],
      self::WIDGET_NEWSLETTER => [
        'name' => _('newsletter'),
        'template' => 'newsletter.tpl',
        'enabled' => true,
      ],
      self::WIDGET_GAMES => [
        'name' => _('games'),
        'template' => 'games.tpl',
        'enabled' => true,
      ],
      self::WIDGET_AOTM => [
        'name' => _('article of the month'),
        'template' => 'articleOfTheMonth.tpl',
        'enabled' => true,
      ],
      self::WIDGET_SOCIAL => [
        'name' => _('social networks'),
        'template' => 'social.tpl',
        'enabled' => false,
      ],
      self::WIDGET_RANDOM_WORD => [
        'name' => _('random word'),
        'template' => 'randomWord.tpl',
        'enabled' => true,
      ],
      self::WIDGET_PROVERB => [
        'name' => _('proverbs'),
        'template' => 'proverb.tpl',
        'enabled' => false,
      ]
    ];
  }

  /**
   * Returns a copy of DATA with the 'enabled' field modified where necessary.
   * widgetCount stores the number of widgets when the user last saved the widgetMask.
   * For new widgets between $widgetCount + 1 and WIDGET_COUNT - 1, which have been added since the last save,
   * we use the widget's default state.
   **/
  static function getWidgets($widgetMask, $widgetCount) {
    $result = self::getData();
    for ($mask = 1; $mask < 1 << $widgetCount; $mask <<= 1) {
      $result[$mask]['enabled'] = ($widgetMask & $mask) ? true : false;
    }
    return $result;
  }

  static function getDefaultWidgetMask() {
    $result = 0;
    foreach (self::getData() as $mask => $params) {
      if ($params['enabled']) {
        $result += $mask;
      }
    }
    return $result;
  }
}
