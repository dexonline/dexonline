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

  // for reporting script conflicts, generated lazily
  private static $scriptMap = null;

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
      $html = self::highlightScriptConflicts($html);
    }
    return $html;
  }

  // Export errors and warnings as flash messages
  static function exportMessages() {
    FlashMessage::bulkAdd(self::$warnings, 'warning');
    FlashMessage::bulkAdd(self::$errors);
  }

  static function buildScriptMap() {
    if (self::$scriptMap === null) {
      self::$scriptMap = [];

      foreach (Constant::UNICODE_SCRIPTS as $scriptName => $scriptRanges) {
        foreach ($scriptRanges as $range) {
          for ($code = $range[0]; $code <= $range[1]; $code++) {
            self::$scriptMap[Str::chr($code)] = $scriptName;
          }
        }
      }

    }
  }

  static function highlightScriptConflicts($s) {
    self::buildScriptMap();

    $prev2 = null;
    $prev2Script = null;
    $prev = null;
    $prevScript = null;
    $result = '';
    $conflicts = [];

    $glyphs = Str::unicodeExplode($s);

    foreach ($glyphs as $glyph) {
      $script = self::$scriptMap[$glyph] ?? null;

      // wrap $prev if it contains a different script than the current glyph
      // of the glyph before $prev
      if ($prevScript &&
          (($prev2Script && ($prev2Script != $prevScript)) ||
           ($script && ($script != $prevScript)))) {
        $result .= "<span class=\"conflictingScripts\">$prev</span>";
        $conflicts[] = ['glyph' => $prev, 'script' => $prevScript];
      } else {
        $result .= $prev;
      }

      $prev2 = $prev;
      $prev2Script = $prevScript;
      $prev = $glyph;
      $prevScript = $script;
    }

    // never wrap the final glyph
    $result .= array_pop($glyphs);

    if (count($conflicts)) {
      FlashMessage::addTemplate('conflictingScripts.tpl',
                                [ 'conflicts' => $conflicts],
                                'warning');
    }

    return $result;
  }

  static function highlightRareGlyphs($s, $rareGlyphs) {
    if (!User::can(User::PRIV_ANY)) {
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
