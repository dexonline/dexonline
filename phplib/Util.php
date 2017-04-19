<?php

// Generic functions that don't belong anywhere else.

class Util {

  const INFINITY = 1000000000;

  // Given an array of sorted arrays, finds the smallest interval that includes
  // at least one element from each array. Named findSnippet in honor of Google.
  static function findSnippet($p) {
    $result = self::INFINITY;
    $n = count($p);
    $indexes = array_pad(array(), $n, 0);
    $done = false;

    while (!$done) {
      $min = self::INFINITY;
      $max = -1;
      for ($i = 0; $i < $n; $i++) {
        $k = $p[$i][$indexes[$i]];
        if ($k < $min) {
          $min = $k;
          $minPos = $i;
        }
        if ($k > $max) {
          $max = $k;
        }
      }
      if ($max - $min < $result) {
        $result = $max - $min;
      }
      if (++$indexes[$minPos] == count($p[$minPos])) {
        $done = true;
      }
    }

    return $result;
  }

}
