<?php

function session_init() {
  // TODO: Optimize this. Load cookie first, then start session if necessary.
  if (util_isWebBasedScript()) {
    if (!session_userExists()) {
      session_loadUserFromCookie();
    }
  }
  // Otherwise we're being called by a local script, not a web-based one.
}

function session_login($user) {
  session_setVariable('user', $user);
  $cookie = new Cookie();
  $cookie->userId = $user->id;
  $cookie->cookieString = util_randomCapitalLetterString(12);
  $cookie->save();
  setcookie("prefs[lll]", $cookie->cookieString,
	    time() + ONE_MONTH_IN_SECONDS);
  log_userLog('Logged in, IP=' . $_SERVER['REMOTE_ADDR']);
  util_redirect(util_getWwwRoot());
}

function session_logout() {
  log_userLog('Logging out, IP=' . $_SERVER['REMOTE_ADDR']);
  $cookieString = session_getCookieSetting('lll');
  $cookie = Cookie::get("cookieString = '$cookieString'");
  if ($cookie) {
    $cookie->delete();
  }
  setcookie("prefs[lll]", NULL, time() - 3600);
  unset($_COOKIE['prefs']['lll']);
  session_start(); // It has to have been started in order to be destroyed.
  if (ini_get("session.use_cookies")) {
    setcookie(session_name(), '', time() - 86400); // expire it
  }
  session_unset();
  session_destroy();
  util_redirect(util_getWwwRoot());
}

// Try to load loing information from the long-lived cookie
function session_loadUserFromCookie() {
  if (!isset($_COOKIE['prefs']) || !isset($_COOKIE['prefs']['lll'])) {
    return;
  }
  $cookie = Cookie::get(sprintf('cookieString = "%s"', $_COOKIE['prefs']['lll']));
  $user = $cookie ? User::get("id={$cookie->userId}") : null;
  if ($user) {
    session_setVariable('user', $user);
  } else {
    // There is a cookie, but it is invalid
    setcookie("prefs[lll]", NULL, time() - 3600);
    unset($_COOKIE['prefs']['lll']);
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
  session_sendAnonymousPrefs();
}

function session_sendAnonymousPrefs() {
  setcookie('prefs[anonymousPrefs]', session_getAnonymousPrefs(), time() + 3600 * 24 * 365, "/");
}

function session_getAnonymousPrefs() {
  $cookiePrefs = session_getCookieSetting('anonymousPrefs');
  return $cookiePrefs ? $cookiePrefs : '';
}

function session_getSkin() {
  $user = session_getUser();
  $skin = ($user && $user->skin) ? $user->skin : session_getCookieSetting('skin');
  if ($skin && session_isValidSkin($skin)) {
    return $skin;
  } else {
    $skins = pref_getServerPreference('skins');
    return $skins[0];
  }
}

function session_setSkin($skin) {
  $_COOKIE['prefs']['skin'] = $skin;
  setcookie('prefs[skin]', session_getSkin(), time() + 3600 * 24 * 365, "/");
}

function session_isValidSkin($skin) {
  return in_array($skin, pref_getServerPreference('skins'));
}

/**
 * Returns an array of the skin-specific preferences defined in the section skin-{$skin}.
 * Returns an empty array if the section is not defined. Never returns false/null.
 **/
function session_getSkinPreferences($skin) {
  $prefs = pref_getServerPreference("skin-{$skin}");
  return $prefs ? $prefs : array();
}

function session_setSourceCookie($source) {
  setcookie('prefs[source]', $source, time() + 3600 * 24 * 365, "/");
}

function session_getDefaultContribSourceId() {
  $value = session_getCookieSetting('source');
  // Previously we stored some short name, not the source id -- just return
  // FALSE in that case
  return is_numeric($value) ? $value : FALSE;
}

function session_isDebug() {
  return session_getUserNick() == pref_getDebugUser();
}

function session_setFlash($message, $type = 'error') {
  $oldMessage = session_variableExists('flashMessage') ? $_SESSION['flashMessage'] : '';
  session_setVariable('flashMessage', "{$oldMessage}{$message}<br/>");
  session_setVariable('flashMessageType', $type);
}

function session_clearFlash() {
  session_unsetVariable('flashMessage');
  session_unsetVariable('flashMessageType');
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
  }
}

function session_variableExists($var) {
  return isset($_SESSION) && isset($_SESSION[$var]);
}

?>
