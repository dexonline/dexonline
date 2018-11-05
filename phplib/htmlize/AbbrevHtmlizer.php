<?php

/**
 * Helper class used to accumulate footnotes as we htmlize a string.
 * */
class AbbrevHtmlizer extends Htmlizer {
  private $ambiguous = [];

  function getKey() {
    return 'abbrevs';
  }

  function getPayload() {
    return $this->ambiguous;
  }

  // htmlize one instance of a abbreviation formatted as #contents#
  function htmlize($match) {
    $abbrevs = Abbrev::loadAbbreviations($this->sourceId);
    $contents = $match[1];
    $matchingKey = Abbrev::bestAbbrevMatch($contents, array_keys($abbrevs));

    if ($matchingKey) {
      $hint = $abbrevs[$matchingKey]['internalRep'];
      // ignore abbreviations within abbreviations... there are, but... c'mon!
      list($hint, $ignored)
        = Str::htmlize($hint, $this->sourceId, $this->errors, $this->warnings);

      // abbreviation long forms are to be printed in the title attribute,
      // therefore we must escape HTML entities
      $hint = htmlspecialchars($hint);
    } else {
      $hint = 'abreviere necunoscută';
      $this->errors[] = "Abreviere necunoscută: «{$contents}».";
    }

    $result = sprintf('<abbr class="abbrev" data-html="true" title="%s">%s</abbr>', $hint, $contents);
    return $result;
  }

}
