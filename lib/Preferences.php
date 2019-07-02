<?php

class Preferences {
  const CEDILLA_BELOW = 0x01;
  const FORCE_DIACRITICS = 0x02;
  const OLD_ORTHOGRAPHY = 0x04;
  const EXCLUDE_UNOFFICIAL = 0x08;
  const SHOW_PARADIGM = 0x10;
  // const LOC_PARADIGM = 0x20; // no longer in use
  const SHOW_ADVANCED = 0x40;
  const PRIVATE_MODE = 0x80;
  const NO_TREES = 0x100;

  // Set of all customizable user preferences
  static $allPrefs = [
    self::CEDILLA_BELOW => [
      'enabled' => true,
      'label' => 'Folosește ş și ţ cu sedilă (în loc de virgulă)',
      'comment' => 'Scrierea corectă este cu &#x219; și &#x21b; în loc de ş și ţ, dar este posibil ca aceste simboluri să nu fie afișate corect în browserul dumneavoastră.',
    ],
    self::FORCE_DIACRITICS => [
      'enabled' => true,
      'label' => 'Pun eu diacritice în căutare',
      'comment' => 'Fără această opțiune, o căutare după „mal” va returna și rezultatele pentru „mâl”. Cu această opțiune, rezultatele pentru „mâl” nu mai sunt returnate decât când căutați explicit „mâl”.',
    ],
    self::OLD_ORTHOGRAPHY => [
      'enabled' => true,
      'label' => 'Folosesc ortografia folosită pînă în 1993 (î din i)',
      'comment' => 'Până în 1993, „&#xe2;” era folosit doar în cuvântul „român”, în cuvintele derivate și în unele nume proprii.',
    ],
    self::EXCLUDE_UNOFFICIAL => [
      'enabled' => true,
      'label' => 'Afișează doar dicționarele canonice',
      'comment' => 'Afișează doar dicționarele canonice editate de Institutul de Lingvistică din cadrul Academiei Române (ultimele ediții ale DEX și DOOM, considerate normative).',
    ],
    self::SHOW_PARADIGM => [
      'enabled' => true,
      'label' => 'Deschide fila de flexiuni',
      'comment' => 'Implicit, prima filă vizibilă la căutări este cea cu definiții.',
    ],
    self::SHOW_ADVANCED => [
      'enabled' => true,
      'label' => 'Afișează meniul de căutare avansată în mod implicit',
      'comment' => 'În mod implicit, meniul de căutare avansată (marcat cu „opțiuni”) va fi afișat pe toate paginile.',
    ],
    self::PRIVATE_MODE => [
      'enabled' => false,
      'label' => 'Modul confidențial',
      'comment' => 'Dezactivează caseta Facebook, reclamele AdSense și alte elemente '
      . 'care divulgă informații despre dumneavoastră unor terțe părți. '
      . '<a href="https://wiki.dexonline.ro/wiki/Modul_confiden%C8%9Bial" '
      . 'target="_blank">Modul confidențial</a> '
      . 'este disponibil timp de un an celor care <a href="doneaza">donează</a> '
      . 'minim 50 de lei.',
    ],
    self::NO_TREES => [
      'enabled' => true,
      'label' => 'Nu arăta definiții structurate',
      'comment' => 'dexonline lucrează la o reprezentare proprie a definițiilor, structurată pe sensuri și subsensuri. Cu această opțiune puteți reveni la formatul original din dicționare.',
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
    // activate the private mode preference for donors
    if ($user && $user->noAdsUntil > time()) {
      $copy[self::PRIVATE_MODE]['enabled'] = true;
    }

    if (Config::GLOBAL_PRIVATE_MODE) {
      $copy[self::PRIVATE_MODE]['comment'] .=
        '<br><b>Notă</b>: Modul confidențial este de acum activat automat pentru toți utilizatorii.';
    }

    return $copy;
  }

  static function getWidgets($user) {
    return $user
      ? Widget::getWidgets($user->widgetMask, $user->widgetCount)
      : Widget::getWidgets(Session::getWidgetMask(), Session::getWidgetCount());
  }

  static function set($user, $detailsVisible, $userPrefs, $widgetMask) {
    if ($user) {
      $user->detailsVisible = $detailsVisible;
      $user->preferences = $userPrefs;
      $user->widgetMask = $widgetMask;
      $user->widgetCount = Widget::WIDGET_COUNT;
      $user->save();
    } else {
      Session::setAnonymousPrefs($userPrefs);
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
