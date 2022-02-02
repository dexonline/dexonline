<?php

class LocaleUtil {
  const COOKIE_NAME = 'locale';

  static $current = null;

  static function init() {
    self::set(self::getCurrent());
  }

  static function getFromConfig() {
    return Config::TEST_MODE ? Config::TEST_LOCALE : Config::DEFAULT_LOCALE;
  }

  // Returns the locale as dictated, in order of priority, by
  // 1. anonymous user preference (cookie)
  // 2. config file
  static function getCurrent() {
    if (!self::$current) {
      self::$current = self::getFromConfig();

      $cookie = $_COOKIE[self::COOKIE_NAME] ?? null;
      if ($cookie && isset(Config::LOCALES[$cookie])) { // sanity check
        self::$current = $cookie;
      }
    }

    return self::$current;
  }

  // Returns the locale with the encoding stripped off, e.g. en_US instead of en_US.utf8
  static function getShort() {
    $l = self::getCurrent();
    return explode('.', $l)[0];
  }

  private static function set($locale) {
    mb_internal_encoding('UTF-8');

    // workaround for Windows lovers
    if (OS::getOS() === OS::OS_WIN) {
      putenv("LC_ALL=$locale");
    }

    setlocale(LC_ALL, $locale);
    $domain = "messages";
    bindtextdomain($domain, Config::ROOT . '/locale');
    bind_textdomain_codeset($domain, 'UTF-8');
    textdomain($domain);
  }

  // changes the locale and stores it in a cookie
  static function change($id) {
    if (!isset(Config::LOCALES[$id])) {
      return;
    }

    // delete the existing cookie if it matches the config value
    if ($id == self::getFromConfig()) {
      Session::unsetCookie(self::COOKIE_NAME);
    } else {
      setcookie(self::COOKIE_NAME, $id, time() + Session::ONE_YEAR_IN_SECONDS, '/');
    }

    self::set($id);
  }

  static function createDateFormatter(string $format) {
    return new IntlDateFormatter(
      self::$current, IntlDateFormatter::NONE, IntlDateFormatter::NONE,
      null, null, $format);
  }

  // Weeks always start on Monday -- this is not localized yet.
  static function getWeekDayNames() {
    $fmt = self::createDateFormatter('EEEE'); // weekday name only

    $result = [];
    foreach (range(0, 6) as $d) {
      // 2018-01-01 fell on a Monday
      $result[] = $fmt->format(new DateTime("2018-01-01 +{$d} days"));
    }

    return $result;
  }

  /**
   * Returns the full month name in the current locale.
   * @param $month an integer or string between 01 and 12.
   */
  static function getMonthName($month) {
    return self::date("2022-{$month}", 'LLLL');
  }

  // formats a number according to the current locale
  static function number($x, $decimals = 0) {
    $locale = localeconv();
    return number_format(
      $x, $decimals, $locale['decimal_point'], $locale['thousands_sep']);
  }

  /**
   * Formats a date according to
   * https://unicode-org.github.io/icu/userguide/format_parse/datetime/
   *
   * @param $value An integer timestamp, a DateTime object or a string
   * parseable by the DateTime constructor.
   */
  static function date($value, string $format = 'd MMM yyyy') {
    $fmt = self::createDateFormatter($format);
    if (is_string($value) && !is_numeric($value)) {
      $value = new DateTime($value);
    }
    return $fmt->format($value);
  }

}
