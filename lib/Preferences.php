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
  static $allPrefs = [
    self::CEDILLA_BELOW => [
      'enabled' => true,
      'label' => 'Folosesc ş și ţ cu sedilă (în loc de virgulă)',
      'comment' => 'Scrierea corectă este cu &#x219; și &#x21b; în loc de ş și ţ, dar este posibil ca aceste simboluri să nu fie afișate corect în browserul dumneavoastră.',
    ],
    self::FORCE_DIACRITICS => [
      'enabled' => true,
      'label' => 'Pun eu diacritice în căutare',
      'comment' => 'Fără această opțiune, o căutare după „mal” va returna și rezultatele pentru „mâl”. Cu această opțiune, rezultatele pentru „mâl” nu mai sunt returnate decât când căutați explicit „mâl”.',
    ],
    self::OLD_ORTHOGRAPHY => [
      'enabled' => true,
      'label' => 'Folosesc ortografia dinainte de 1993 (î din i)',
      'comment' => 'Până în 1993, „&#xe2;” era folosit doar în cuvântul „român”, în cuvintele derivate și în unele nume proprii.',
    ],
    self::NORMATIVE_ONLY => [
      'enabled' => true,
      'label' => 'Afișează doar dicționarele normative',
      'comment' => 'Afișează doar dicționarele normative editate de Institutul de Lingvistică din cadrul Academiei Române (ultimele ediții ale DEX și DOOM).',
    ],
    self::SHOW_ADVANCED => [
      'enabled' => true,
      'label' => 'Afișează meniul de căutare avansată în mod implicit',
      'comment' => 'În mod implicit, meniul de căutare avansată (marcat cu „opțiuni”) va fi afișat pe toate paginile.',
    ],
  ];

  static function getDetailsVisible($user) {
    return $user ? $user->detailsVisible : false;
  }

  /* Returns a copy of self::$allPrefs with an extra field 'checked' set to true or false. */
  static function getUserPrefs($user) {
    $userPrefs = $user ? $user->preferences : Session::getAnonymousPrefs();
    $copy = self::$allPrefs;
    // Set the checked field to false / true according to user preferences
    foreach ($copy as $key => $value) {
      $copy[$key]['checked'] = false;
    }

    if ($userPrefs) {
      foreach (self::$allPrefs as $key => $value) {
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

  static function set($user, $detailsVisible, $userPrefs, $preferredTab, $widgetMask) {
    if ($user) {
      $user->detailsVisible = $detailsVisible;
      $user->preferences = $userPrefs;
      $user->preferredTab = $preferredTab;
      $user->widgetMask = $widgetMask;
      $user->widgetCount = Widget::WIDGET_COUNT;
      $user->save();
    } else {
      Session::setAnonymousPrefs($userPrefs);
      Session::setPreferredTab($preferredTab);
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
