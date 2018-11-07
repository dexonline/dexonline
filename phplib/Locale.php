<?php

class Locale {
  const COOKIE_NAME = 'locale';
  private static $available;

  static function init() {
    self::$available = Config::get('global.availableLocales');
    self::set();
  }

  // Sets the locale as dictated, in order of priority, by
  // 1. logged in user preference
  // 2. anonymous user preference (cookie)
  // 3. config file
  private static function set() {
    mb_internal_encoding('UTF-8');

    // read config value
    $locale = Config::get('testing.enabled')
      ? Config::get('testing.locale')
      : Config::get('global.locale');

    // read cookie value and clear the cookie if it matches the config value
    if (isset($_COOKIE[self::COOKIE_NAME])) {
      $cookie = $_COOKIE[self::COOKIE_NAME];
      if ($cookie == $locale){
        Session:unsetCookie(self::COOKIE_NAME);
      } else{
        $locale = $cookie;
      }
    }

    // TODO read user pref

    setlocale(LC_ALL, $locale);
    $domain = "messages";
    bindtextdomain($domain, Core::getRootPath() . '/locale');
    bind_textdomain_codeset($domain, 'UTF-8');
    textdomain($domain);
  }

  // formats a number according to the current locale
  static function number($x, $decimals = 0) {
    $locale = localeconv();
    return number_format(
      $x, $decimals, $locale['decimal_point'], $locale['thousands_sep']);
  }

}
