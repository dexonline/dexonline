<?php

class Session {

  const ONE_MONTH_IN_SECONDS = 30 * 86400;
  const ONE_YEAR_IN_SECONDS = 365 * 86400;

  static function init() {
    if (isset($_COOKIE[session_name()])) {
      session_start();
    }
    if (!Config::MAINTENANCE && Request::isWeb()) {
      self::setActiveUser();
    }
    // Otherwise we're being called by a local script, not a web-based one.
  }

  static function login($user, $remember = false) {
    self::set('userId', $user->id);
    if ($remember) {
      $cookie = Cookie::create($user->id);
      self::setCookie('prefs[lll]', $cookie->cookieString);
    }

    User::setActive($user->id); // for logging purposes only
    Log::info('Logged in, IP=' . $_SERVER['REMOTE_ADDR']);
    Util::redirectToHome();
  }

  static function logout() {
    Log::info('Logged out, IP=' . $_SERVER['REMOTE_ADDR']);
    $cookieString = self::getCookieSetting('lll');
    $cookie = Cookie::get_by_cookieString($cookieString);
    if ($cookie) {
      $cookie->delete();
    }
    self::unsetCookie('prefs[lll]');
    unset($_COOKIE['prefs']['lll']);
    self::kill();
    Util::redirectToHome();
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
        self::unsetCookie('prefs[lll]');
        unset($_COOKIE['prefs']['lll']);
        if ($cookie) {
          $cookie->delete();
        }
      }
    }
  }

  static function getCookieSetting($name, $default = '') {
    return $_COOKIE['prefs'][$name] ?? $default;
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
      $preferences = self::getAnonymousPrefs();
    }

    return (int)$preferences & $pref;
  }

  /**
   * Returns a list of search tabs ordered by user preferences or. If no
   * preference is set, returns tabs in the default order.
   */
  static function getTabs() {
    $u = User::getActive();

    $tabOrder = $u
      ? $u->tabOrder
      : self::getCookieSetting('tabOrder', 0);

    return ($tabOrder == 0)
      ? Tab::ORDER
      : Tab::unpack($tabOrder);
  }

  static function setTabOrder(int $tabOrder) {
    // Remove the cookie rather than setting it to its default value.
    if ($tabOrder) {
      self::setCookie('prefs[tabOrder]', $tabOrder);
    } else {
      self::unsetCookie('prefs[tabOrder]');
    }
  }

  static function setAnonymousPrefs($pref) {
    // Remove the cookie rather than setting it to its default value.
    if ($pref) {
      self::setCookie('prefs[anonymousPrefs]', $pref);
    } else {
      self::unsetCookie('prefs[anonymousPrefs]');
    }
  }

  static function getAnonymousPrefs() {
    return self::getCookieSetting('anonymousPrefs');
  }

  static function getWidgetMask() {
    return self::getCookieSetting('widgetMask', 0);
  }

  static function setWidgetMask($widgetMask) {
    if ($widgetMask === null) {
      self::unsetCookie('prefs[widgetMask]');
    } else {
      self::setCookie('prefs[widgetMask]', $widgetMask);
    }
  }

  static function getWidgetCount() {
    return self::getCookieSetting('widgetCount', 0);
  }

  static function setWidgetCount($widgetCount) {
    if ($widgetCount === null) {
      self::unsetCookie('prefs[widgetCount]');
    } else {
      self::setCookie('prefs[widgetCount]', $widgetCount);
    }
  }

  static function setSourceCookie($source) {
    self::setCookie('prefs[source]', $source);
  }

  static function getSourceCookie() {
    return self::getCookieSetting('source');
  }

  static function get($name, $default = null) {
    return $_SESSION[$name] ?? $default;
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

  static function setCookie(string $name, string $value) {
    setcookie($name, $value, time() + self::ONE_YEAR_IN_SECONDS, '/');
  }

  static function unsetCookie($name) {
    unset($_COOKIE[$name]);
    setcookie($name, '', time() - 3600, '/');
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
    if (ini_get('session.use_cookies')) {
      self::unsetCookie(session_name());
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
      self::setCookie("prefs[{$cookieName}]", '1');
      FlashMessage::add($onMessage, 'success');
    } else {
      self::unsetCookie("prefs[{$cookieName}]");
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
      self::setCookie('prefs[diffGranularity]', $newLevel);
    } else {
      self::unsetCookie('prefs[diffGranularity]');
    }

    $name = DiffUtil::GRANULARITY_NAMES[$newLevel];
    FlashMessage::add("Diferențe la nivel de {$name}.", 'warning');
  }
}
