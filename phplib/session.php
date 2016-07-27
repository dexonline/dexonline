<?php

define("ONE_MONTH_IN_SECONDS", 30 * 86400);
define("ONE_YEAR_IN_SECONDS", 365 * 86400);

function session_init() {
  if (isset($_COOKIE[session_name()])) {
    session_start();
  }
  if (util_isWebBasedScript()) {
    if (!session_userExists()) {
      session_loadUserFromCookie();
    }
  }
  // Otherwise we're being called by a local script, not a web-based one.
}

function session_login($user, $openidData) {
  if (!$user) {
    $user = Model::factory('User')->create();
  }
  if (!$user->identity) {
    $user->identity = $openidData['identity'];
  }
  if (!$user->openidConnectSub && isset($openidData['sub'])) {
    $user->openidConnectSub = $openidData['sub'];
  }
  if (!$user->nick && isset($openidData['nickname'])) {
    $user->nick = $openidData['nickname'];
  }
  if (isset($openidData['fullname'])) {
    $user->name = $openidData['fullname'];
  }
  if (isset($openidData['email'])) {
    $user->email = $openidData['email'];
  }
  $user->password = null; // no longer necessary after the first OpenID login
  $user->save();

  session_setVariable('user', $user);
  $cookie = Model::factory('Cookie')->create();
  $cookie->userId = $user->id;
  $cookie->cookieString = util_randomCapitalLetterString(12);
  $cookie->save();
  setcookie("prefs[lll]", $cookie->cookieString, time() + ONE_MONTH_IN_SECONDS, '/');
  Log::info('Logged in, IP=' . $_SERVER['REMOTE_ADDR']);
  util_redirect(util_getWwwRoot());
}

function session_logout() {
  Log::info('Logged out, IP=' . $_SERVER['REMOTE_ADDR']);
  $cookieString = session_getCookieSetting('lll');
  $cookie = Cookie::get_by_cookieString($cookieString);
  if ($cookie) {
    $cookie->delete();
  }
  setcookie("prefs[lll]", NULL, time() - 3600, '/');
  unset($_COOKIE['prefs']['lll']);
  session_kill();
  util_redirect(util_getWwwRoot());
}

// Try to load logging information from the long-lived cookie
function session_loadUserFromCookie() {
  if (!isset($_COOKIE['prefs']) || !isset($_COOKIE['prefs']['lll'])) {
    return;
  }
  $cookie = Cookie::get_by_cookieString($_COOKIE['prefs']['lll']);
  $user = $cookie ? User::get_by_id($cookie->userId) : null;
  if ($user && $user->identity) {
    session_setVariable('user', $user);
  } else {
    // The cookie is invalid or this account doesn't have an OpenID identity yet.
    setcookie("prefs[lll]", NULL, time() - 3600, '/');
    unset($_COOKIE['prefs']['lll']);
    if ($cookie) {
      $cookie->delete();
    }
  }
}

function session_getCookieSetting($name) {
  if (array_key_exists('prefs', $_COOKIE)) {
    $prefsCookie = $_COOKIE['prefs'];
    if (array_key_exists($name, $prefsCookie)) {
      return $prefsCookie[$name];
    }
  }
  return FALSE;
}

function session_userExists() {
  return session_variableExists('user') && isset($_SESSION['user']->id);
}

function session_getUser() {
  if (!session_userExists()) {
    return FALSE;
  }

  return $_SESSION['user'];
}

function session_getUserNick() {
  return session_variableExists('user') && isset($_SESSION['user']->nick)
    ? $_SESSION['user']->nick : "Anonim";
}

function session_getUserId() {
  return session_variableExists('user') && isset($_SESSION['user']->id)
    ? $_SESSION['user']->id : 0;
}

function session_user_prefers($pref) {
  if (isset($_SESSION['user'])) {
    return isset($_SESSION['user']->preferences) && in_array($pref, preg_split('/,/', $_SESSION['user']->preferences));
  } else {
    $prefs = session_getCookieSetting('anonymousPrefs');
    return in_array($pref, preg_split('/,/', $prefs));
  }
}

function session_setAnonymousPrefs($pref) {
  $_COOKIE['prefs']['anonymousPrefs'] = $pref;
  setcookie('prefs[anonymousPrefs]', $pref, time() + ONE_YEAR_IN_SECONDS, '/');
}

function session_getAnonymousPrefs() {
  $cookiePrefs = session_getCookieSetting('anonymousPrefs');
  return $cookiePrefs ? $cookiePrefs : '';
}

function session_getWidgetMask() {
  return session_getCookieSetting('widgetMask');
}

function session_setWidgetMask($widgetMask) {
  $_COOKIE['prefs']['widgetMask'] = $widgetMask;
  setcookie('prefs[widgetMask]', $widgetMask, time() + ONE_YEAR_IN_SECONDS, '/');
}

function session_getWidgetCount() {
  return session_getCookieSetting('widgetCount');
}

function session_setWidgetCount($widgetCount) {
  $_COOKIE['prefs']['widgetCount'] = $widgetCount;
  setcookie('prefs[widgetCount]', $widgetCount, time() + ONE_YEAR_IN_SECONDS, '/');
}

function session_setSourceCookie($source) {
  setcookie('prefs[source]', $source, time() + ONE_YEAR_IN_SECONDS, '/');
}

function session_getDefaultContribSourceId() {
  $value = session_getCookieSetting('source');
  // Previously we stored some short name, not the source id -- just return
  // FALSE in that case
  return is_numeric($value) ? $value : FALSE;
}

function session_get($name) {
  return session_getWithDefault($name, null);
}

function session_getWithDefault($name, $default) {
  if (isset($_SESSION)){
    if (array_key_exists($name, $_SESSION)) {
      return $_SESSION[$name];
    }
  }
  return $default;
}

function session_setVariable($var, $value) {
  // Lazy start of the session so we don't send a PHPSESSID cookie unless we have to
  if (!isset($_SESSION)) {
    session_start();
  }
  $_SESSION[$var] = $value;
}

function session_unsetVariable($var) {
  if (isset($_SESSION)) {
    unset($_SESSION[$var]);
    if (!count($_SESSION)) {
      // Note that this will prevent us from creating another session this same request.
      // This does not seem to cause a problem at the moment.
      session_kill();
    }
  }
}

function session_variableExists($var) {
  return isset($_SESSION) && isset($_SESSION[$var]);
}

function session_kill() {
  if (!isset($_SESSION)) {
    session_start(); // It has to have been started in order to be destroyed.
  }
  session_unset();
  @session_destroy();
  if (ini_get("session.use_cookies")) {
    setcookie(session_name(), '', time() - 3600, '/'); // expire it
  }
}

?>
