<?php

class Locale {
  const COOKIE_NAME = 'locale';
  private static $available;

  static function init() {
    self::$available = Config::get('global.availableLocales');
    self::set(self::getCurrent());
  }

  static function getAll() {
    return self::$available;
  }

  static function getFromConfig() {
    return Config::get('testing.enabled')
      ? Config::get('testing.locale')
      : Config::get('global.locale');
  }

  // Returns the locale as dictated, in order of priority, by
  // 1. logged in user preference
  // 2. anonymous user preference (cookie)
  // 3. config file
  static function getCurrent() {
    $locale = self::getFromConfig();

    if (isset($_COOKIE[self::COOKIE_NAME])) {
      $locale = $_COOKIE[self::COOKIE_NAME];
    }

    return $locale;
  }

  private static function set($locale) {
    mb_internal_encoding('UTF-8');

    // TODO read user pref

    setlocale(LC_ALL, $locale);
    $domain = "messages";
    bindtextdomain($domain, Core::getRootPath() . '/locale');
    bind_textdomain_codeset($domain, 'UTF-8');
    textdomain($domain);
  }

  // changes the locale and stores it in the user preferences
  static function change($id) {
    $current = self::getFromConfig();

    // TODO set user pref

    if ($current == $id) {
      // delete the existing cookie if it matches the config value
      Session::unsetCookie(self::COOKIE_NAME);
    } else {
      setcookie(self::COOKIE_NAME, $id, time() + Session::ONE_YEAR_IN_SECONDS, '/');
    }

    self::set($id);
  }

  // formats a number according to the current locale
  static function number($x, $decimals = 0) {
    $locale = localeconv();
    return number_format(
      $x, $decimals, $locale['decimal_point'], $locale['thousands_sep']);
  }

}
