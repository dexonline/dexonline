<?php

/**
 * Keyboard-aware implementation of the Levenshtein distance. Assigns lower scores to neighboring pairs of letters.
 */
class Levenshtein {

  //                             a  b  c  d  e  f  g  h  i  j  k  l  m  n  o  p  q  r  s  t  u  v  w  x  y  z
  private static $COORD_X = array(0, 4, 2, 2, 2, 3, 4, 5, 7, 6, 7, 8, 6, 5, 8, 9, 0, 3, 1, 4, 6, 3, 1, 1, 5, 0);
  private static $COORD_Y = array(1, 2, 2, 1, 0, 1, 1, 1, 0, 1, 1, 1, 2, 2, 0, 0, 0, 0, 1, 0, 0, 2, 0, 2, 0, 2);

  private static $COST_INS = 5;
  private static $COST_DEL = 5;
  private static $COST_TRANSPOSE = 5;
  private static $DIST_HORIZ = 5;
  private static $DIST_VERT = 10;
  private static $DIST_OTHER = 15;
  private static $INFTY = 100000;

  // Computes the distance between two letters
  private static function letterDistance($c1, $c2) {
    if ($c1 == $c2) {
      return 0;
    }
    if ($c1 < 'a' || $c1 > 'z' || $c2 < 'a' || $c2 > 'z') {
      return self::$DIST_OTHER;
    }
    $ord1 = ord($c1) - ord('a');
    $ord2 = ord($c2) - ord('a');
    $x1 = self::$COORD_X[$ord1];
    $y1 = self::$COORD_Y[$ord1];
    $x2 = self::$COORD_X[$ord2];
    $y2 = self::$COORD_Y[$ord2];
    if ($y1 == $y2 && abs($x1 - $x2) == 1) {
      return self::$DIST_HORIZ;
    }
    if ((($y2 == $y1 - 1) && (($x2 == $x1) || ($x2 == $x1 + 1))) ||
        (($y1 == $y2 - 1) && (($x1 == $x2) || ($x1 == $x2 + 1)))) {
      return self::$DIST_VERT;
    }
    return self::$DIST_OTHER;
  }

  static function dist($s1, $s2) {
    $s1 = mb_strtolower(Str::unicodeToLatin($s1));
    $s2 = mb_strtolower(Str::unicodeToLatin($s2));

    $len1 = mb_strlen($s1);
    $len2 = mb_strlen($s2);

    // Split the strings into characters to minimize the number calls to getCharAt().
    $chars1 = array();
    for ($i = 0; $i < $len1; $i++) {
      $chars1[] = Str::getCharAt($s1, $i);
    }
    $chars2 = array();
    for ($j = 0; $j < $len2; $j++) {
      $chars2[] = Str::getCharAt($s2, $j);
    }

    // Initialize the first row and column of the matrix
    $a = array();
    for ($i = 0; $i <= $len1; $i++) {
      $a[$i][0] = $i * self::$DIST_OTHER;
    }
    for ($j = 0; $j <= $len2; $j++) {
      $a[0][$j] = $j * self::$COST_DEL;
    }
       
    // Compute the rest of the matrix with the custom Levenshtein algorithm
    for ($i = 0; $i < $len1; $i++) {
      for ($j = 0; $j < $len2; $j++) {
        $mati = $i + 1;
        $matj = $j + 1;

        // Delete
        $a[$mati][$matj] = $a[$mati][$matj - 1] + self::$COST_DEL;

        // Insert
        $costInsert = ($i == 0) ? self::$INFTY : max(self::$COST_INS, self::letterDistance($chars1[$i], $chars1[$i - 1])); // At least COST_INS
        $a[$mati][$matj] = min($a[$mati][$matj], $a[$mati - 1][$matj] + $costInsert);

        // Modify (This includes the case where $s1[i] == $s2[j] because dist(x, x) returns 0)
        $a[$mati][$matj] = min($a[$mati][$matj], $a[$mati - 1][$matj - 1] + self::letterDistance($chars1[$i], $chars2[$j]));
               
        // Transpose
        if ($i && $j && ($chars1[$i] == $chars2[$j - 1]) && ($chars1[$i - 1] == $chars2[$j])) {
          $a[$mati][$matj] = min($a[$mati][$matj], $a[$mati - 2][$matj - 2] + self::$COST_TRANSPOSE);
        }
      }
    }

    return $a[$len1][$len2];
  }
}

?>
