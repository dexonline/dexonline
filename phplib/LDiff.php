<?php

/**
 * Takes two arrays and reports the differences between them. Uses a Levenshtein-like algorithm:
 * D(i,j) =
 *   D(i + 1, j + 1)                    if V[i] == V[j]
 *   1 + min(D(i + 1, j), D(i, j + 1)   otherwise
 *
 * When it retraces the path, it collapses consecutive insertions or deletions.
 **/

class LDiff {

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
  static function textDiff($old, $new) {
    return self::diff(explode(' ', $old), explode(' ', $new));
  }

  static function htmlDiff($old, $new) {
    $result = '';
    $owords = explode(' ', $old);
    $nwords = explode(' ', $new);
    $diff = self::diff($owords, $nwords);
    $i = $j = 0;
    foreach ($diff as list($ostart, $olen, $nstart, $nlen)) {
      assert($ostart - $i == $nstart - $j);
      $result .= implode(' ', array_slice($owords, $i, $ostart - $i)) . ' ';
      $result .= '<del>' . implode(' ', array_slice($owords, $ostart, $olen)) . '</del> ';
      $result .= '<ins>' . implode(' ', array_slice($nwords, $nstart, $nlen)) . '</ins> ';

      $i = $ostart + $olen;
      $j = $nstart + $nlen;
    }

    $result .= implode(' ', array_slice($owords, $i));

    return $result;
  }
}

?>
