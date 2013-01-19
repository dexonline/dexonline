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

  static function get($user) {
    $detailsVisible = $user ? $user->detailsVisible : false;
    $userPrefs = $user ? $user->preferences : session_getAnonymousPrefs();
    $prefCopy = self::$allPrefs;

    // Set the checked field to false / true according to user preferences
    foreach ($prefCopy as $key => $value) {
      $prefCopy[$key]['checked'] = false;
    }
    if ($userPrefs) {
      foreach (preg_split('/,/', $userPrefs) as $pref) {
        $prefCopy[$pref]['checked'] = true;
      }
    }
    return array($detailsVisible, $prefCopy, session_getSkin());
  }

  static function set($user, $detailsVisible, $userPrefs, $skin) {
    if ($user) {
      $user->detailsVisible = $detailsVisible;
      $user->preferences = $userPrefs;
      if (session_isValidSkin($skin)) {
        $user->skin = $skin;
      }
      $user->save();
      session_setVariable('user', $user);
    } else {
      session_setAnonymousPrefs($userPrefs);
      if (session_isValidSkin($skin)) {
        session_setSkin($skin);
      }
    }
  }
}

?>
