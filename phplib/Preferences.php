<?php

class Preferences {
  // TODO: We should work with numeric constants, but this will break existing cookies.
  const CEDILLA_BELOW = 'CEDILLA_BELOW';
  const FORCE_DIACRITICS = 'FORCE_DIACRITICS';
  const OLD_ORTHOGRAPHY = 'OLD_ORTHOGRAPHY';
  const EXCLUDE_UNOFFICIAL = 'EXCLUDE_UNOFFICIAL';
  const SHOW_PARADIGM = 'SHOW_PARADIGM';

  // Set of all customizable user preferences
  public static $allPrefs = array(
    self::CEDILLA_BELOW => array(
      'label' => 'Vreau să văd ş și ţ cu sedilă (în loc de virguliță)',
      'comment' => 'Scrierea corectă este cu &#x219; și &#x21b; în loc de ş și ţ, dar este posibil ca aceste simboluri să nu fie afișate corect în browserul dumneavoastră.',
    ),
    self::FORCE_DIACRITICS => array(
      'label' => 'Pun eu diacritice în căutare',
      'comment' => 'Fără această opțiune, o căutare după „mal” va returna și rezultatele pentru „mâl”. Cu această opțiune, rezultatele pentru „mâl” nu mai sunt returnate decât când căutați explicit „mâl”.',
    ),
    self::OLD_ORTHOGRAPHY => array(
      'label' => 'Folosesc ortografia folosită pînă în 1993 (î din i)',
      'comment' => 'Până în 1993, „&#xe2;” era folosit doar în cuvântul „român”, în cuvintele derivate și în unele nume proprii.',
    ),
    self::EXCLUDE_UNOFFICIAL => array(
      'label' => 'Vreau să vizualizez numai definițiile „oficiale”',
      'comment' => 'Sursele „neoficiale” nu au girul niciunei instituții acreditate de Academia Română sau al vreunei edituri de prestigiu.', 
    ),
    self::SHOW_PARADIGM => array(
      'label' => 'Doresc ca flexiunile să fie expandate',
      'comment' => 'Implicit, flexiunile sunt ascunse.', 
    ),
  );

  static function getDetailsVisible($user) {
    return $user ? $user->detailsVisible : false;
  }

  /* Returns a copy of self::$allPrefs with an extra field 'checked' set to true or false. */
  static function getUserPrefs($user) {
    $userPrefs = $user ? $user->preferences : session_getAnonymousPrefs();
    $copy = self::$allPrefs;
    // Set the checked field to false / true according to user preferences
    foreach ($copy as $key => $value) {
      $copy[$key]['checked'] = false;
    }
    if ($userPrefs) {
      foreach (preg_split('/,/', $userPrefs) as $pref) {
        $copy[$pref]['checked'] = true;
      }
    }
    return $copy;
  }

  static function getSkin($user) {
    return session_getSkin();
  }

  static function getWidgets($user) {
    return $user
      ? Widget::getWidgets($user->widgetMask, $user->widgetCount)
      : Widget::getWidgets(session_getWidgetMask(), session_getWidgetCount());
  }

  static function set($user, $detailsVisible, $userPrefs, $skin, $widgetMask) {
    if ($user) {
      $user->detailsVisible = $detailsVisible;
      $user->preferences = $userPrefs;
      if (session_isValidSkin($skin)) {
        $user->skin = $skin;
      }
      $user->widgetMask = $widgetMask;
      $user->widgetCount = Widget::WIDGET_COUNT;
      $user->save();
      session_setVariable('user', $user);
    } else {
      session_setAnonymousPrefs($userPrefs);
      if (session_isValidSkin($skin)) {
        session_setSkin($skin);
      }
      // Set the widgetMask / widgetCount cookies. This is a bit complex because we want to delete the cookie when the settings are all default.
      if ($widgetMask == Widget::getDefaultWidgetMask()) {
        session_setWidgetMask(null);
        session_setWidgetCount(null);
      } else {
        session_setWidgetMask($widgetMask);
        session_setWidgetCount(Widget::WIDGET_COUNT);
      }
    }
  }
}

?>
