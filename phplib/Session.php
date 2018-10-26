<?php

class Session {

  const ONE_MONTH_IN_SECONDS = 30 * 86400;
  const ONE_YEAR_IN_SECONDS = 365 * 86400;

  static function init() {
    if (isset($_COOKIE[session_name()])) {
      session_start();
    }
    if (Request::isWeb()) {
      self::setActiveUser();
    }
    // Otherwise we're being called by a local script, not a web-based one.
  }

  static function login($user, $remember = false) {
    self::set('userId', $user->id);
    if ($remember) {
      $cookie = Cookie::create($user->id);
      setcookie("prefs[lll]", $cookie->cookieString, time() + self::ONE_YEAR_IN_SECONDS, '/');
    }

    User::setActive($user->id); // for logging purposes only
    Log::info('Logged in, IP=' . $_SERVER['REMOTE_ADDR']);
    Util::redirect(Core::getWwwRoot());
  }

  static function logout() {
    Log::info('Logged out, IP=' . $_SERVER['REMOTE_ADDR']);
    $cookieString = self::getCookieSetting('lll');
    $cookie = Cookie::get_by_cookieString($cookieString);
    if ($cookie) {
      $cookie->delete();
    }
    setcookie("prefs[lll]", NULL, time() - 3600, '/');
    unset($_COOKIE['prefs']['lll']);
    self::kill();
    Util::redirect(Core::getWwwRoot());
  }

  // Try to load logging information from the long-lived cookie
  static function loadUserFromCookie() {
    $lll = self::getCookieSetting('lll');
    if ($lll) {
      $cookie = Cookie::get_by_cookieString($lll);
      $user = $cookie ? User::get_by_id($cookie->userId) : null;
      if ($user) {
        self::set('userId', $user->id);
        User::setActive($user->id);
      } else {
        // The cookie is invalid.
        setcookie("prefs[lll]", NULL, time() - 3600, '/');
        unset($_COOKIE['prefs']['lll']);
        if ($cookie) {
          $cookie->delete();
        }
      }
    }
  }

  static function getCookieSetting($name, $default = '') {
    if (array_key_exists('prefs', $_COOKIE)) {
      $prefsCookie = $_COOKIE['prefs'];
      if (array_key_exists($name, $prefsCookie)) {
        return $prefsCookie[$name];
      }
    }
    return $default;
  }

  static function setActiveUser() {
    if ($userId = self::get('userId')) {
      User::setActive($userId);
    } else {
      self::loadUserFromCookie();
    }
  }

  static function userPrefers($pref) {
    $u = User::getActive();
    if ($u) {
      $preferences = $u->preferences;
    } else {
      $preferences = self::getCookieSetting('anonymousPrefs');
    }

    $preferences = Preferences::convert($preferences);

    return $preferences & $pref;
  }

  static function setAnonymousPrefs($pref) {
    $_COOKIE['prefs']['anonymousPrefs'] = $pref;
    setcookie('prefs[anonymousPrefs]', $pref, time() + self::ONE_YEAR_IN_SECONDS, '/');
  }

  static function getAnonymousPrefs() {
    return self::getCookieSetting('anonymousPrefs');
  }

  static function getWidgetMask() {
    return self::getCookieSetting('widgetMask', 0);
  }

  static function setWidgetMask($widgetMask) {
    $_COOKIE['prefs']['widgetMask'] = $widgetMask;
    setcookie('prefs[widgetMask]', $widgetMask, time() + self::ONE_YEAR_IN_SECONDS, '/');
  }

  static function getWidgetCount() {
    return self::getCookieSetting('widgetCount', 0);
  }

  static function setWidgetCount($widgetCount) {
    $_COOKIE['prefs']['widgetCount'] = $widgetCount;
    setcookie('prefs[widgetCount]', $widgetCount, time() + self::ONE_YEAR_IN_SECONDS, '/');
  }

  static function setSourceCookie($source) {
    setcookie('prefs[source]', $source, time() + self::ONE_YEAR_IN_SECONDS, '/');
  }

  static function getSourceCookie() {
    return self::getCookieSetting('source');
  }

  static function get($name, $default = null) {
    if (isset($_SESSION)){
      if (array_key_exists($name, $_SESSION)) {
        return $_SESSION[$name];
      }
    }
    return $default;
  }

  static function set($var, $value) {
    // Lazy start of the session so we don't send a PHPSESSID cookie unless we have to
    if (!isset($_SESSION)) {
      session_start();
    }
    $_SESSION[$var] = $value;
  }

  static function unsetVar($var) {
    if (isset($_SESSION)) {
      unset($_SESSION[$var]);
      if (!count($_SESSION)) {
        // Note that this will prevent us from creating another session this same request.
        // This does not seem to cause a problem at the moment.
        self::kill();
      }
    }
  }

  static function has($var) {
    return isset($_SESSION) && isset($_SESSION[$var]);
  }

  static function kill() {
    if (!isset($_SESSION)) {
      session_start(); // It has to have been started in order to be destroyed.
    }
    session_unset();
    @session_destroy();
    if (ini_get("session.use_cookies")) {
      setcookie(session_name(), '', time() - 3600, '/'); // expire it
    }
  }

  static function isStructureMode() {
    return isset($_COOKIE['prefs']['structureMode']);
  }

  static function isWotdMode() {
    return isset($_COOKIE['prefs']['wotdMode']);
  }

  static function toggleMode($cookieName, $onMessage, $offMessage) {
    $on = !isset($_COOKIE['prefs'][$cookieName]);

    if ($on) {
      setcookie("prefs[{$cookieName}]", '1', time() + self::ONE_YEAR_IN_SECONDS, '/');
      FlashMessage::add($onMessage, 'success');
    } else {
      setcookie("prefs[{$cookieName}]", '', time() - 3600, '/');
      FlashMessage::add($offMessage, 'warning');
    }
  }

  static function getDiffGranularity() {
    return self::getCookieSetting('diffGranularity', DiffUtil::DEFAULT_GRANULARITY);
  }

  static function cycleDiffGranularity() {
    $currentLevel = self::getDiffGranularity();
    $newLevel = ($currentLevel + 1) % DiffUtil::NUM_GRANULARITIES;

    if ($newLevel != DiffUtil::DEFAULT_GRANULARITY) {
      setcookie('prefs[diffGranularity]', $newLevel, time() + self::ONE_YEAR_IN_SECONDS, '/');
    } else {
      setcookie('prefs[diffGranularity]', '', time() - 3600, '/');
    }

    $name = DiffUtil::$GRANULARITY_NAMES[$newLevel];
    FlashMessage::add("Diferențe la nivel de {$name}.", 'warning');
  }
}
