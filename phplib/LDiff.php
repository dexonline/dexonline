<?php

/**
 * Takes two arrays and reports the differences between them. Uses a Levenshtein-like algorithm:
 * D(i,j) =
 *   D(i + 1, j + 1)                    if V[i] == V[j]
 *   1 + min(D(i + 1, j), D(i, j + 1)   otherwise
 *
 * When it retraces the path, it collapses consecutive insertions or deletions.
 **/

ini_set('memory_limit', '1024M');

class LDiff {
  const SPLIT_LEVEL_LETTER = 0;
  const SPLIT_LEVEL_WORD = 1;
  const DEFAULT_SPLIT_LEVEL = LDiff::SPLIT_LEVEL_WORD;

  public static $SPLIT_LEVEL = [
    LDiff::SPLIT_LEVEL_LETTER => '',
    LDiff::SPLIT_LEVEL_WORD => ' ',
  ];

  // Returns an array of modifications. Each entry in the array is a tuple:
  // ($offsetOld, $lengthOld, $offsetNew, $lengthNew)
  static function diff($old, $new) {
    $results = array();
    $oc = count($old);
    $nc = count($new);

    // Pad the matrix
    $d = array();
    for ($i = 0; $i <= $oc; $i++) {
      $d[$i][$nc] = $oc - $i;
    }
    for ($i = 0; $i <= $nc; $i++) {
      $d[$oc][$i] = $nc - $i;
    }

    // Levenshtein
    for ($i = $oc - 1; $i >= 0; $i--) {
      for ($j = $nc - 1; $j >= 0; $j--) {
        $d[$i][$j] = ($old[$i] == $new[$j])
          ? $d[$i + 1][$j + 1]
          : (1 + min($d[$i + 1][$j], $d[$i][$j + 1]));
      }
    }

    // Build the diff array
    $i = $j = 0;    // current coords
    $i0 = $j0 = 0;  // start of the last diff pair
    while ($i < $oc || $j < $nc) {
      if (($i < $oc) && ($j < $nc) && ($old[$i] == $new[$j])) {
        if ($i0 != $i || $j0 != $j) {
          $results[] = array($i0, $i - $i0, $j0, $j - $j0);
        }
        $i++;
        $j++;
        $i0 = $i;
        $j0 = $j;
      } else if (($i < $oc) && ($d[$i][$j] == 1 + $d[$i + 1][$j])) {
        $i++;
      } else {
        $j++;
      }
    }

    // once more at the end
    if ($i0 != $i || $j0 != $j) {
      $results[] = array($i0, $i - $i0, $j0, $j - $j0);
    }

    return $results;
  }

  // text diff works at word level
  static function textDiff($old, $new, $splitLevel = LDiff::DEFAULT_SPLIT_LEVEL) {
    $sep = LDiff::$SPLIT_LEVEL[$splitLevel];
    return self::diff(preg_split("/{$sep}/", $old),
                      preg_split("/{$sep}/", $new));
  }

  // returns a degree of dissimilarity between two strings.
  static function diffMeasure($old, $new) {
    $diff = self::textDiff($old, $new);
    $result = 0;
    foreach ($diff as list($ostart, $olen, $nstart, $nlen)) {
      $result += $olen + $nlen;
    }
    return $result;
  }

  // A clickable diff adds offset information to each <ins> and <del> tag.
  // Javascript can use this to, for example, fix typos with a single click.
  static function htmlDiff($old, $new, $clickable = false) {
    $splitLevel = session_getSplitLevel();
    $sep = LDiff::$SPLIT_LEVEL[$splitLevel];

    // Break the strings into words.
    $result = '';
    $owords = preg_split("/{$sep}/u", $old, null, PREG_SPLIT_NO_EMPTY);
    $nwords = preg_split("/{$sep}/u", $new, null, PREG_SPLIT_NO_EMPTY);

    // Compute the offset of each word
    $ooff = array();
    foreach ($owords as $i => $ignored) {
      $ooff[$i] = $i ? ($ooff[$i - 1] + strlen($owords[$i - 1]) + 1) : 0;
    }
    $ooff[] = strlen($old);
    $noff = array();
    foreach ($nwords as $i => $ignored) {
      $noff[$i] = $i ? ($noff[$i - 1] + strlen($nwords[$i - 1]) + 1) : 0;
    }
    $noff[] = strlen($new);

    // Compute the diff
    $diff = self::diff($owords, $nwords);

    // Assemble the HTML
    $i = $j = 0;
    foreach ($diff as list($ostart, $olen, $nstart, $nlen)) {
      assert($ostart - $i == $nstart - $j);
      $common = implode(LDiff::$SPLIT_LEVEL[$splitLevel], array_slice($owords, $i, $ostart - $i));
      $deleted = implode(LDiff::$SPLIT_LEVEL[$splitLevel], array_slice($owords, $ostart, $olen));
      $inserted = implode(LDiff::$SPLIT_LEVEL[$splitLevel], array_slice($nwords, $nstart, $nlen));

      $result .= $common . ' ';
      if ($clickable) {
        $result .= sprintf('<span class="diff" data-start1="%s" data-len1="%s" data-start2="%s" data-len2="%s">',
                           $ooff[$ostart],
                           strlen($deleted),
                           $noff[$nstart],
                           strlen($inserted));
      }
      $result .= "<del>{$deleted}</del> <ins>{$inserted}</ins> ";
      if ($clickable) {
        $result .= '</span>';
      }

      $i = $ostart + $olen;
      $j = $nstart + $nlen;
    }

    $result .= implode(LDiff::$SPLIT_LEVEL[$splitLevel], array_slice($owords, $i)); // final common part

    return $result;
  }
}

?>
