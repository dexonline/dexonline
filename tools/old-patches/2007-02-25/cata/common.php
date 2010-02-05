<?
require_once("../../../phplib/util.php");
ini_set('max_execution_time', '3600');
ini_set("memory_limit", "512000000");
set_magic_quotes_runtime(0);
assert_options(ASSERT_BAIL, 1);
debug_off();

DEFINE('REG_TR_OPEN', '/<tr[^>]*>/');
DEFINE('REG_TR_CLOSE', '/<\/tr>/');
DEFINE('REG_TD', '/<td[^>]*>([^<]*)<\/td>/');
DEFINE('LEGAL_WORD_CHARACTERS', "aăâbcdefghiîjklmnopqrsștțuvwxyz'");

function parseArguments() {
  global $argv;
  $verbose = false;
  $fileName = FILE_NAME;
  for ($i = 1; $i < count($argv); $i++) {
    $arg = $argv[$i];
    if ($arg == "-v") {
      $verbose = true;
    } else if ($arg == '-f') {
      $i++;
      $fileName = $argv[$i];
    } else if ($arg == '-t') {
      runTestSuite();
      exit;
    } else {
      os_errorAndExit("Unknown flag: $arg");
    }
  }
  return array($verbose, $fileName);
}

function readAndFormatFile($fileName) {
  $fp = fopen($fileName, 'r');
  $data = fread($fp, 100000000);
  fclose($fp);
  $data = mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1');
  $data = str_replace(array("\n", "\r", '&amp;', '&quot;', '&#7855;',
			    '<span style="">&nbsp;</span>',
			    '&#258;', '&#259;', '&#350;',
			    '&#351;', '&#354;', '&#355;'),
		      array(' ', ' ', '&', '"', 'ă',
			    '',
			    'Ă', 'ă', 'Ș',
			    'Ș', 'Ț', 'ț'),
		      $data);
  $data = preg_replace('/<span style="">(&nbsp;| )*<\/span>/', '', $data);
  $data = preg_replace('/<font class="font\d+">/', '', $data);
  $data = str_replace('</font>', '', $data);

  // Collapse multiple spaces
  $data = preg_replace('/ +/', ' ', $data);
  $data = text_unicodeToLower($data);
  return $data;
}

function dprint($message) {
  global $verbose;
  if ($verbose) {
    print "$message\n";
  }
}

function dprintArray($a, $name) {
  global $verbose;
  if ($verbose) {
    print "$name:";
    foreach ($a as $form) {
      print ' {' . $form . '}';
    }
    print "\n";
  }
}

/**
 * Performs a regexp match and advances the data pointer.
 */
function matchRegexp($regexp) {
  global $data;
  global $pos;
  global $matches;

  $result = preg_match($regexp, $data, $matches, PREG_OFFSET_CAPTURE, $pos);
  if ($result) {
    $pos = $matches[0][1] + strlen($matches[0][0]);
  }
  return $result;
}

/**
 * Performs a regexp match, but does not advance the data pointer.
 */
function testRegexp($regexp) {
  global $data;
  global $pos;
  global $matches;

  return preg_match($regexp, $data, $matches, PREG_OFFSET_CAPTURE, $pos);
}

/**
 * Captures the next table row and returns an array of the cell values.
 * Returns null if there are no more <tr> tags.
 */
function captureTr() {
  global $matches;
  global $pos;
  
  if (!matchRegexp(REG_TR_OPEN)) {
    return null;
  }
  
  testRegexp(REG_TR_CLOSE);
  $closePos = $matches[0][1] + strlen($matches[0][0]);
  
  $result = array();
  $done = false;
  do {
    $anyTd = testRegexp(REG_TD);
    $startTd = $anyTd ? $matches[0][1] : 0;
    if (!$anyTd || $startTd > $closePos) {
      $done = true;
    } else {
      $contents = trim($matches[1][0]);
      if ($contents) {
        $result[] = $contents;
      }
      $pos = $matches[0][1] + strlen($matches[0][0]);
    }
  } while (!$done);
  $pos = $closePos;
  return $result;
}

function assertEquals($expected, $actual) {
  if ($expected != $actual) {
    print "Assertion failed.\n";
    print "  expected [$expected]\n";
    print "  actual   [$actual]\n";
    debug_print_backtrace();
    exit;
  }
}

function assertEqualArrays($expected, $actual) {
  assertEquals(count($expected), count($actual));
  for ($i = 0; $i < count($expected); $i++) {
    assertEquals($expected[$i], $actual[$i]);
  }
}

function normalizeForm($form) {
  // Special case 1: staro-s/ș-ti or staro-s/ș-tii or staro-s/ș-tilor
  if (text_startsWith($form, 'staro-s/ș-ti')) {
    $rest = mb_substr($form, 12);
    return array("starosti$rest", "staroști$rest");
  }

  // Special case 2: [a]iastalaltă (-tă-)
  if ($form == "[a]iastalaltă (-tă-)") {
    return array("aiastalaltă", "iastalaltă");
  }

  $form = str_replace(array('-', 'á', 'é', 'í', 'ó', 'ú'),
                      array('', "'a", "'e", "'i", "'o", "'u"), $form);
  $form = trim($form);
  return normalizeFormRecursively($form);
}

function normalizeFormRecursively($form) {
  $form = trim($form);

  if (!$form) {
    return array();
  }

  // expr (expr): Used by the verbs "vrea" and "avea"
  $matches = array();
  if (preg_match('/^([^(]+) \(([^)]+)\)$/', $form, $matches)) {
    return array_merge(normalizeFormRecursively($matches[1]),
                       normalizeFormRecursively($matches[2]));
  }

  // expr [expr]: Used by some imperative forms, e.g. adormi [adoarme]
  if (preg_match('/^([^\[]+) \[([^\]]+)\]$/', $form, $matches)) {
    return array_merge(normalizeFormRecursively($matches[1]),
                       normalizeFormRecursively($matches[2]));
  }

  // expr / expr
  $parts = split('/', $form);
  if (count($parts) > 1) {
    $results = array();
    foreach ($parts as $part) {
      $results = array_merge($results, normalizeFormRecursively($part));
    }
    return $results;
  }

  // expr1 | expr2: concats every result of expr1 with every result of expr2
  $parts = split('\|', $form, 2);
  if (count($parts) == 2) {
    $left = normalizeFormRecursively($parts[0]);
    $right = normalizeFormRecursively($parts[1]);
    $results = array();
    foreach ($left as $l) {
      foreach ($right as $r) {
        $results[] = $l . $r;
      }
    }
    return $results;
  }

  // expr1, expr2: used in conjunction with | to list prefixes or suffixes.
  $parts = split(',', $form);
  if (count($parts) > 1) {
    $results = array();
    foreach ($parts as $part) {
      $results = array_merge($results, normalizeFormRecursively($part));
    }
    return $results;
  }

  // a[b]c: return abc and ac. Here, a and b are assumed to be terminals.
  if (preg_match('/^([^\[]*)\[([^\]]*)\](.*)$/', $form, $matches)) {
    $a = $matches[1];
    $b = $matches[2];
    $rest = normalizeFormRecursively($matches[3]);
    if (!count($rest)) {
      $rest[] = '';
    }
    $results = array();
    foreach ($rest as $r) {
      $results[] = $a . $b . $r;
      $results[] = $a . $r;
    }
    return $results;
  }  

  return array($form);
}

function runTestSuite() {
  assertEqualArrays(array("mama"),
                    normalizeForm("mama"));
  assertEqualArrays(array("m'ama", "t'ata"),
                    normalizeForm(" m'ama    ( t'ata   )   "));
  assertEqualArrays(array("a", "b", "c", "d", "e"),
                    normalizeForm("a/b/c (d / e)"));
  assertEqualArrays(array("starostilor", "staroștilor"),
                    normalizeForm("staro-s/ș-tilor"));
  assertEqualArrays(array("abcde", "acde", "abce", "ace"),
                    normalizeForm("a[b]c[d]e"));
  assertEqualArrays(array("abef", "abgh", "cdef", "cdgh"),
                    normalizeForm("ab-, cd- | -ef, -gh"));
}

function saveCommonModel($modelType, $modelNumber, $forms, $descr,
                         $inflections) {
  $model = Model::create($modelType, $modelNumber, $descr);
  $model->save();

  $modelNumber = addslashes($modelNumber);

  $baseForm = '';
  foreach ($forms as $index => $form) {
    $inflectionId = $inflections[$index];
    $variants = normalizeForm($form);

    foreach ($variants as $variantNo => $variant) {
      if (!text_validateAlphabet($variant, LEGAL_WORD_CHARACTERS)) {
        die("Illegal characters in form [$variant]\n");
      }
      if (!$variant) {
        // Skip missing inflections
        continue;
      }

      $variant = addslashes($variant);
      if (!$baseForm) {
        $baseForm = $variant;
      }
      //print "[$baseForm]\t[$inflectionId]\t[$variantNo]\t[$variant]\n";
      mysql_query("insert into dmlr_models set model_type = '$modelType', " .
                  "model_no = '$modelNumber', form = '$variant', " .
                  "infl_id = $inflectionId, variant = $variantNo");
    }
  }

  assert($baseForm != '');
  mysql_query("insert into model_exponents set model_type = '$modelType', " .
              "model_no = '$modelNumber', form = '$baseForm'");
}

?>
