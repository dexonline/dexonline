<?php

/**
 * This class reads request parameters.
 **/
class Request {
  /* Reads a request parameter. */
  static function get($name, $default = null) {
    return array_key_exists($name, $_REQUEST)
      ? $_REQUEST[$name]
      : $default;
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
        util_redirect(util_getWwwRoot());
      }
      $short = $e->getShortDescription();
      $url = "intrare{$sourcePart}/{$short}/{$e->id}/{$paradigmPart}";
    } else if ($lexemId) {
      $l = Lexem::get_by_id($lexemId);
      if (!$l) {
        util_redirect(util_getWwwRoot());
      }
      $url = "lexem/{$l->formNoAccent}/{$l->id}";
    } else {
      $url = "definitie{$sourcePart}/{$cuv}{$paradigmPart}";
    }

    util_redirect(util_getWwwRoot() . $url . $allPart);
  }

}

?>
