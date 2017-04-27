<?php

class Preferences {
  // TODO: We should work with numeric constants, but this will break existing cookies.
  const CEDILLA_BELOW = 'CEDILLA_BELOW';
  const FORCE_DIACRITICS = 'FORCE_DIACRITICS';
  const OLD_ORTHOGRAPHY = 'OLD_ORTHOGRAPHY';
  const EXCLUDE_UNOFFICIAL = 'EXCLUDE_UNOFFICIAL';
  const SHOW_PARADIGM = 'SHOW_PARADIGM';
  const LOC_PARADIGM = 'LOC_PARADIGM';
  const SHOW_ADVANCED = 'SHOW_ADVANCED';
  const PRIVATE_MODE = 'PRIVATE_MODE';

  // Set of all customizable user preferences
  public static $allPrefs = [
    self::CEDILLA_BELOW => [
      'enabled' => true,
      'label' => 'Folosește ş și ţ cu sedilă (în loc de virguliță)',
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
      'label' => 'Ascunde definițiile neoficiale',
      'comment' => 'Sursele neoficiale nu au girul niciunei instituții acreditate de Academia Română sau al vreunei edituri de prestigiu.', 
    ],
    self::SHOW_PARADIGM => [
      'enabled' => true,
      'label' => 'Expandează flexiunile',
      'comment' => 'Implicit, flexiunile sunt ascunse.', 
    ],
    self::LOC_PARADIGM => [
      'enabled' => true,
      'label' => 'Arată formele ilegale la jocul de scrabble',
      'comment' => 'La afișarea paradigmei, aceste forme flexionare vor apărea cu roșu.', 
    ],
    self::SHOW_ADVANCED => [
      'enabled' => true,
      'label' => 'Afișează meniul de căutare avansată în mod implicit',
      'comment' => 'În mod implicit, meniul de căutare avansată (marcat cu \'opțiuni\') va fi afișat pe toate paginile.', 
    ],
    self::PRIVATE_MODE => [
      'enabled' => false,
      'label' => 'Modul privat',
      'comment' => 'Dezactivează caseta Facebook, reclamele AdSense și alte elemente care divulgă informații despre dumneavoastră unor terțe părți. Modul privat este disponibil timp de un an celor care <a href="doneaza">donează</a> minim 50 de lei.',
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
      foreach (preg_split('/,/', $userPrefs) as $pref) {
        if (isset($copy[$pref])) {
          $copy[$pref]['checked'] = true;
        }
      }
    }
    // activate the private mode preference for donors
    // TODO: script to revoke private mode upon expiration
    if ($user && $user->noAdsUntil > time()) {
      $copy[self::PRIVATE_MODE]['enabled'] = true;
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
      Session::set('user', $user);
    } else {
      Session::setAnonymousPrefs($userPrefs);
      // Set the widgetMask / widgetCount cookies. This is a bit complex because we want to delete the cookie when the settings are all default.
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

?>
