<?php

class Preferences {
  const CEDILLA_BELOW = 0x01;
  const FORCE_DIACRITICS = 0x02;
  const OLD_ORTHOGRAPHY = 0x04;
  const NORMATIVE_ONLY = 0x08;
  // 0x10 and 0x20 are no longer in use. The DB values have been cleared. Feel
  // free to reuse when needed.
  const SHOW_ADVANCED = 0x40;
  // 0x80 is no longer in use. Clear the DB values before reusing.

  // Set of all customizable user preferences
  public static function getAllPrefs() {
    return [
      self::CEDILLA_BELOW => [
        'enabled' => true,
        'label' => _('I use ș and ț with a cedilla (instead of a comma)'),
        'comment' => _('The correct spelling is with ș and ț instead of ș and ț, but these symbols may not be displayed correctly in your browser.'),
      ],
      self::FORCE_DIACRITICS => [
        'enabled' => true,
        'label' => _('I put diacritics in the search'),
        'comment' => _('Without this option, a search for "mal" will also return results for "mâl". With this option, the results for "mâl" are no longer returned unless you explicitly search for "mâl".'),
      ],
      self::OLD_ORTHOGRAPHY => [
        'enabled' => true,
        'label' => _('I use the pre-1993 orthography (î from i)'),
        'comment' => _('Until 1993, "â" was used only in the word "român", in derived words and in some proper names.'),
      ],
      self::NORMATIVE_ONLY => [
        'enabled' => true,
        'label' => _('Display only normative dictionaries'),
        'comment' => _('Displays only the normative dictionaries edited by the Institute of Linguistics of the Romanian Academy (the latest editions of DEX and DOOM).'),
      ],
      self::SHOW_ADVANCED => [
        'enabled' => true,
        'label' => _('Show advanced search menu by default'),
        'comment' => _('By default, the advanced search menu (marked with "options") will be displayed on all pages.'),
      ],
    ];
  }

  static function getDetailsVisible($user) {
    return $user ? $user->detailsVisible : false;
  }

  /* Returns a copy of self::getAllPrefs() with an extra field 'checked' set to true or false. */
  static function getUserPrefs($user) {
    $userPrefs = $user ? $user->preferences : Session::getAnonymousPrefs();
    $copy = self::getAllPrefs();
    // Set the checked field to false / true according to user preferences
    foreach ($copy as $key => $value) {
      $copy[$key]['checked'] = false;
    }

    if ($userPrefs) {
      foreach (self::getAllPrefs() as $key => $value) {
        if ($userPrefs & $key) {
          $copy[$key]['checked'] = true;
        }
      }
    }

    return $copy;
  }

  static function getWidgets($user) {
    return $user
      ? Widget::getWidgets($user->widgetMask, $user->widgetCount)
      : Widget::getWidgets(Session::getWidgetMask(), Session::getWidgetCount());
  }

  static function set($user, $detailsVisible, $userPrefs, $tabs, $widgetMask) {
    $tabOrder = Tab::isDefaultOrder($tabs) ? 0 : Tab::pack($tabs);
    if ($user) {
      $user->detailsVisible = $detailsVisible;
      $user->preferences = $userPrefs;
      $user->tabOrder = $tabOrder;
      $user->widgetMask = $widgetMask;
      $user->widgetCount = Widget::WIDGET_COUNT;
      $user->save();
    } else {
      Session::setAnonymousPrefs($userPrefs);
      Session::setTabOrder($tabOrder);
      // Set the widgetMask / widgetCount cookies.
      // This is a bit complex, because we want to delete the cookie when the settings
      // are all default.
      if ($widgetMask == Widget::getDefaultWidgetMask()) {
        Session::setWidgetMask(null);
        Session::setWidgetCount(null);
      } else {
        Session::setWidgetMask($widgetMask);
        Session::setWidgetCount(Widget::WIDGET_COUNT);
      }
    }
  }

}
