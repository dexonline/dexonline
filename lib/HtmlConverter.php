<?php

/**
 * Static class that converts internal notations to HTML. Works with any
 * object that has an internalRep field. Collects errors and warnings
 * encountered in the process.
 *
 * Easier to implement as a static class since it is unclear how to
 * instantiate and reuse a converter.
 **/

class HtmlConverter {
  private static $errors = [];
  private static $warnings = [];

  static function convert($obj) {
    if (!$obj) {
      return null;
    }

    $sourceId = $obj->sourceId ?? 0;
    list($html, $footnotes)
      = Str::htmlize($obj->internalRep, $sourceId, self::$errors, self::$warnings);

    if ($obj instanceof Definition) {
      $obj->setFootnotes($footnotes);
      $html = self::highlightRareGlyphs($html, $obj->rareGlyphs);
    }
    return $html;
  }

  // Export errors and warnings as flash messages
  static function exportMessages() {
    FlashMessage::bulkAdd(self::$warnings, 'warning');
    FlashMessage::bulkAdd(self::$errors);
  }

  static function highlightRareGlyphs($s, $rareGlyphs) {
    if (!User::can(User::PRIV_ANY) || !$rareGlyphs) {
      return $s;
    }

    // We must assume there is some HTML in the definition. There is no safe
    // point in time where we can assume there isn't, because (a) we want to
    // remove footnotes before highlighting rare glyphs and (b) as soon as we
    // remove a footnote, we insert a <sup>[1]</sup> in its place. Therefore,
    // the code below must skip HTML tags.
    $rareMap = array_fill_keys(Str::unicodeExplode($rareGlyphs), true);
    $result = '';
    $inTag = false;

    foreach (Str::unicodeExplode($s) as $glyph) {
      if ($glyph == '<') {
        $inTag = true;
      }
      if (!$inTag && isset($rareMap[$glyph])) {
        $result .= "<span class=\"rareGlyph\">$glyph</span>";
      } else {
        $result .= $glyph;
      }
      if ($glyph == '>') {
        $inTag = false;
      }
    }

    return $result;
  }

}
