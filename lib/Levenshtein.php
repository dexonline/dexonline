<?php

/**
 * Keyboard-aware implementation of the Levenshtein distance.
 *
 * If on Linux, this attempts to call the C prograp lib/c/levenshtein. If
 * that fails, it falls back to the PHP implementation, which is 100 times
 * slower.
 *
 * Refer to lib/c/levenshtein.c for details on the Levenshtein
 * implementation.
 **/
class Levenshtein {

  const COST_INS = 10;
  const COST_DEL = 10;
  const COST_TRANSPOSE = 5;
  const COST_KBD = 8;
  const COST_DIACRITICS = 2;
  const COST_OTHER = 15;
  const INFTY = 10000;
  const MAX_DISTANCE = 20;
  const MAX_LENGTH = 100;

  const KEY_PAIRS = [
    "qw", "we", "er", "rt", "ty", "yu", "ui", "io", "op",
    "as", "sd", "df", "fg", "gh", "hj", "jk", "kl",
    "zx", "xc", "cv", "vb", "bn", "nm",
    "qa", "ws", "ed", "rf", "tg", "yh", "uj", "ik", "ol",
    "wa", "es", "rd", "tf", "yg", "uh", "ij", "ok", "pl",
    "az", "sx", "dc", "fv", "gb", "hn", "jm",
    "sz", "dx", "fc", "gv", "hb", "jn", "km",
  ];
  const DIACRITICS = ['ă', 'â', 'î', 'ș', 'ț'];

  private static $symbolDist;
  private static $queryChars;
  private static $mat;
  private static $prevEndRow = 0;

  static function init() {
    // initialize the first row and column of $mat
    self::$mat = [];
    for ($i = 0; $i < self::MAX_LENGTH; $i++) {
      self::$mat[$i][0] = $i * self::COST_INS;
      self::$mat[0][$i] = $i * self::COST_DEL;
    }

    // populate $symbolDist with some values lower than the default distance
    $m = &self::$symbolDist; // syntactic sugar

    foreach (self::KEY_PAIRS as $p) {
      $m[$p[0]][$p[1]] = $m[$p[1]][$p[0]] = self::COST_KBD;
    }

    // add diacritics
    foreach (self::DIACRITICS as $dia) {
      $latin = Str::unicodeToLatin($dia);
      $m[$latin][$dia] = $m[$dia][$latin] = self::COST_DIACRITICS;
    }
  }

  private static function dist(&$chars, $startRow, $maxDistance) {
    // syntactic sugar; also 10-15% faster
    $m = &self::$mat;
    $qc = &self::$queryChars;
    $sd = &self::$symbolDist;

    $minRowDist = 0;
    $d = self::INFTY;

    for ($i = min($startRow, self::$prevEndRow);
         ($i < count($chars)) && ($minRowDist <= $maxDistance);
         $i++) {
      $x = $chars[$i];
      $minRowDist = self::INFTY;

      foreach ($qc as $j => $y) {
        if ($x == $y) {
          $d = $m[$i][$j];
        } else {
          // transpose
          if ($i && $j &&
              ($x == $qc[$j - 1]) &&
              ($chars[$i - 1] == $y)) {
            $d = $m[$i - 1][$j - 1] + self::COST_TRANSPOSE;
          } else {
            $d = self::INFTY;
          }

          // delete, insert, modify
          $d = min(
            $d,
            $m[$i + 1][$j] + self::COST_DEL,
            $m[$i][$j + 1] + self::COST_INS,
            $m[$i][$j] + ($sd[$x][$y] ?? self::COST_OTHER)
          );

        }

        $m[$i + 1][$j + 1] = $d;
        $minRowDist = min($minRowDist, $d);
      }
    }

    self::$prevEndRow = $i;
    return $d;
  }

  private static function closestPhp($query, $maxDistance) {
    // note: parsing the file ourselves does NOT speed things up
    $lines = file(FileCache::getCompactFormsFileName());

    self::$queryChars = Str::unicodeExplode($query);
    $results = [];
    $chars = [];

    foreach ($lines as $l) {
      $common = (int)($l[0]);
      $suffix = trim(substr($l, 1));

      $chars = array_merge(
        array_slice($chars, 0, $common),
        Str::unicodeExplode($suffix)
      );

      $d = self::dist($chars, $common, $maxDistance);
      if ($d <= $maxDistance) {
        $results[] = [ $d, implode($chars) ];
      }
    }

    return $results;
  }

  private static function closestC($query, $maxDistance) {
    if (PHP_OS != 'Linux') {
      throw new Exception('Not on GNU/Linux');
    }

    $fileName = FileCache::getCompactFormsFileName();
    $command = sprintf('%s/c/levenshtein "%s" %d %s',
                       __DIR__, addslashes($query), $maxDistance, $fileName);

    exec($command, $output, $status);
    if ($status != 0) {
      throw new Exception("Cannot execute the levenshtein binary, status code {$status}");
    }

    return array_map(function ($line) {
      return explode(' ', $line, 2);
    }, $output);
  }

  static function closest($query, $maxResults = 10, $maxDistance = self::MAX_DISTANCE) {
    // ensure the compact forms file exists
    $fileName = FileCache::getCompactFormsFileName();
    if (!file_exists($fileName)) {
      self::genCompactForms();
    }

    // run the C program or fall back to the PHP code
    try {
      $results = self::closestC($query, $maxDistance);
    } catch (Exception $e) {
      Log::warning('Cannot execute C version of Levenshtein search: %s',
                   $e->getMessage());
      $results = self::closestPhp($query, $maxDistance);
    }

    // now $results is an array of tuples (distance, string);
    // split them into two arrays
    $dist = [];
    $str = [];
    foreach ($results as $tuple) {
      $dist[] = $tuple[0];
      $str[] = $tuple[1];
    }

    // sort them by distance
    array_multisort($dist, $str);

    // return just the strings and at most $maxResults of them
    return array_slice($str, 0, $maxResults);
  }

  static function genCompactForms() {
    ini_set('memory_limit', '512M');

    $prevForm = '';
    $prev = [];

    $forms = Model::factory('Lexeme')
      ->select('formNoAccent')
      ->order_by_asc('formNoAccent')
      ->find_array();

    $s = '';

    foreach ($forms as $form) {
      $f = mb_strtolower($form['formNoAccent']);
      if ($f != $prevForm) {
        $chars = Str::unicodeExplode($f);

        $i = 0;
        while ($i < count($chars) &&
               $i < count($prev) &&
               $i < 9 && // common prefix must be single digit
               $chars[$i] == $prev[$i]) {
          $i++;
        }
        assert($i < count($chars));
        $s .= $i . mb_substr($f, $i) . "\n";
        $prev = $chars;
        $prevForm = $f;
      }
    }

    FileCache::putCompactForms($s);
  }

}

Levenshtein::init();
