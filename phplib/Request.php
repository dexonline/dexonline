<?php

/**
 * This class reads request parameters.
 **/
class Request {

  /* Cleans up a request parameter recursively. */
  /* $apostrpohes: if true, clean them up. If false, leave them untouched. */
  static function cleanup($x, $apostrophes = true) {
    if (is_string($x)) {
      return Str::cleanup($x, $apostrophes);
    } else if (is_array($x)) {
      $result = [];
      foreach ($x as $key => $value) {
        $result[$key] = self::cleanup($value, $apostrophes);
      }
      return $result;
    } else if (is_object($x)) { // for example, JSON decodes as objects
      $result = new stdClass();
      foreach ($x as $key => $value) {
        $result->$key = self::cleanup($value, $apostrophes);
      }
      return $result;
    } else {
      return $x;
    }
  }

  /* Reads a request parameter. Cleans up string and array values. */
  static function get($name, $default = null) {
    if (!array_key_exists($name, $_REQUEST)) {
      return $default;
    } else {
      return self::cleanup($_REQUEST[$name]);
    }
  }

  /* Same, but leaves apostrophes untouched. */
  static function getWithApostrophes($name, $default = null) {
    if (!array_key_exists($name, $_REQUEST)) {
      return $default;
    } else {
      return self::cleanup($_REQUEST[$name], false);
    }
  }

  /* Reads a request parameter. Performs no cleanup. */
  static function getRaw($name, $default = null) {
    if (!array_key_exists($name, $_REQUEST)) {
      return $default;
    } else {
      return $_REQUEST[$name];
    }
  }

  /* Reads a file record from $_FILES. */
  static function getFile($name, $default = null) {
    return array_key_exists($name, $_FILES)
      ? $_FILES[$name]
      : $default;
  }

  /* Reads a present-or-not parameter (checkbox, button etc.). */
  static function has($name) {
    return array_key_exists($name, $_REQUEST);
  }

  /* Returns an array of values from a parameter in CSV format */
  static function getCsv($name) {
    return explode(',', self::get($name, []));
  }

  /* Use when the parameter is expected to have array type. */
  static function getArray($name) {
    $val = self::get($name);
    return empty($val) ? [] : $val;
  }

  /**
   * Use when the parameter is encoded JSON.
   * Note that the JSON string must be decoded before cleanup. Otherwise entities like „”
   * can be replaced with "", which will corrupt the JSON string.
   **/
  static function getJson($name, $default = null, $assoc = false) {
    if (!array_key_exists($name, $_REQUEST)) {
      return $default;
    } else {
      $json = $_REQUEST[$name];
      $obj = json_decode($json, $assoc);
      return self::cleanup($obj);
    }
  }

  /**
   * Returns true if this script is running in response to a web request, false
   * otherwise.
   */
  static function isWeb() {
    return isset($_SERVER['REMOTE_ADDR']);
  }

  static function isAjax() {
    return isset($_SERVER['REQUEST_URI']) &&
      Str::startsWith($_SERVER['REQUEST_URI'], Config::URL_PREFIX . 'ajax/');
  }

  static function getFullServerUrl() {
    $protocol = Config::PROTOCOL;
    $host = $_SERVER['SERVER_NAME'];
    $port =  $_SERVER['SERVER_PORT'];
    $path = Config::URL_PREFIX;

    return ($port == '80')
      ? "{$protocol}://{$host}{$path}"
      : "{$protocol}://{$host}:{$port}{$path}";
  }

  /**
   * Search engine friendly URLs used for the search page:
   * 1) https://dexonline.ro/definitie[-<sursa>]/<cuvânt>[/<defId>][/paradigma]
   * 2) https://dexonline.ro/lexem[-<sursa>]/<cuvânt>[/<lexemeId>][/paradigma]
   * 3) https://dexonline.ro/text[-<sursa>]/<text>
   * Links of the old form (search.php?...) can only come via the search form and
   * should not contain lexemeId / definitionId.
   */
  static function redirectToFriendlyUrl(
    $cuv, $entryId, $lexemeId, $sourceUrlName, $text, $tab, $format, $all
  ) {
    if (strpos($_SERVER['REQUEST_URI'], '/search.php?') === false) {
      return;    // The url is already friendly.
    }

    if ($format['name'] != 'html') {
      return;
    }

    $cuv = urlencode($cuv);
    $sourceUrlName = urlencode($sourceUrlName);

    $sourcePart = $sourceUrlName ? "-{$sourceUrlName}" : '';
    $tabPart = Constant::TAB_URL[$tab];
    $allPart = ($all && ($tab == Constant::TAB_RESULTS)) ? '/expandat' : '';

    if ($text) {
      $url = "text{$sourcePart}/{$cuv}";
    } else if ($entryId) {
      $e = Entry::get_by_id($entryId);
      if (!$e) {
        Util::redirect(Config::URL_PREFIX);
      }
      $short = $e->getShortDescription();
      $url = "intrare{$sourcePart}/{$short}/{$e->id}{$tabPart}";
    } else if ($lexemeId) {
      $l = Lexeme::get_by_id($lexemeId);
      if (!$l) {
        Util::redirect(Config::URL_PREFIX);
      }
      $url = "lexem/{$l->formNoAccent}/{$l->id}";
    } else {
      $url = "definitie{$sourcePart}/{$cuv}{$tabPart}";
    }

    Util::redirect(Config::URL_PREFIX . $url . $allPart);
  }

}
