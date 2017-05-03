<?php

/**
 * This class reads request parameters.
 **/
class Request {
  /* Reads a request parameter. */
  static function get($name, $default = null) {
    if (!array_key_exists($name, $_REQUEST)) {
      return $default;
    } else if (is_string($_REQUEST[$name])) {
      return AdminStringUtil::cleanup($_REQUEST[$name]);
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

  /**
   * Returns true if this script is running in response to a web request, false
   * otherwise.
   */
  static function isWeb() {
    return isset($_SERVER['REMOTE_ADDR']);
  }

  static function isAjax() {
    return isset($_SERVER['REQUEST_URI']) &&
      StringUtil::startsWith($_SERVER['REQUEST_URI'], Core::getWwwRoot() . 'ajax/');
  }

  static function getFullServerUrl() {
    $host = $_SERVER['SERVER_NAME'];
    $port =  $_SERVER['SERVER_PORT'];
    $path = Core::getWwwRoot();

    return ($port == '80') ? "http://$host$path" : "http://$host:$port$path";
  }

  /**
   * Search engine friendly URLs used for the search page:
   * 1) https://dexonline.ro/definitie[-<sursa>]/<cuvânt>[/<defId>][/paradigma]
   * 2) https://dexonline.ro/lexem[-<sursa>]/<cuvânt>[/<lexemId>][/paradigma]
   * 3) https://dexonline.ro/text[-<sursa>]/<text>
   * Links of the old form (search.php?...) can only come via the search form and
   * should not contain lexemId / definitionId.
   */
  static function redirectToFriendlyUrl($cuv, $entryId, $lexemId, $sourceUrlName, $text,
                                        $showParadigm, $format, $all) {
    if (strpos($_SERVER['REQUEST_URI'], '/search.php?') === false) {
      return;    // The url is already friendly.
    }

    if ($format['name'] != 'html') {
      return;
    }

    $cuv = urlencode($cuv);
    $sourceUrlName = urlencode($sourceUrlName);

    $sourcePart = $sourceUrlName ? "-{$sourceUrlName}" : '';
    $paradigmPart = $showParadigm ? '/paradigma' : '';
    $allPart = ($all && !$showParadigm) ? '/expandat' : '';

    if ($text) {
      $url = "text{$sourcePart}/{$cuv}";
    } else if ($entryId) {
      $e = Entry::get_by_id($entryId);
      if (!$e) {
        Util::redirect(Core::getWwwRoot());
      }
      $short = $e->getShortDescription();
      $url = "intrare{$sourcePart}/{$short}/{$e->id}/{$paradigmPart}";
    } else if ($lexemId) {
      $l = Lexem::get_by_id($lexemId);
      if (!$l) {
        Util::redirect(Core::getWwwRoot());
      }
      $url = "lexem/{$l->formNoAccent}/{$l->id}";
    } else {
      $url = "definitie{$sourcePart}/{$cuv}{$paradigmPart}";
    }

    Util::redirect(Core::getWwwRoot() . $url . $allPart);
  }

}

?>
