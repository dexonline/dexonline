<?
require_once("common.php");
DEFINE('FILE_NAME', '/tmp/loc4-list.html');
DEFINE('LEGAL_LEXEM_CHARACTERS', "aăâbcdefghiîjklmnopqrsștțuvwxyzäåçéöüù'");
$GLOBALS['modelTypes'] = array('A', 'I', 'F', 'M', 'MF', 'N', 'P', 'V', 'VT');
list($verbose, $fileName) = parseArguments();
$data = readAndFormatFileL($fileName);
$dataLen = strlen($data);

$done = false;
$headerRow = true;
$pos = 0;
$numLines = 0;
$unparsedRecords = array();

while (!$done) {
  list($token, $pos) = getNextToken($pos);
  $done = !$token;
  if ($token == '<tr>') {
    $record = array();
    do {
      // Inside each <td> tag, we can have multiple bits of text separated
      // by <font> tags, as is the case with BOKMAL. Glue them together.
      list($trToken, $pos) = getNextToken($pos);
      if ($trToken != '</tr>') {
        if ($trToken != '<td>') {
          die("Expected <td>, got $trToken\n");
        }
        $text = '';
        do {
          list($tdToken, $pos) = getNextToken($pos);
          if ($tdToken[0] != '<') {
            $text .= $tdToken;
          }
        } while ($tdToken != '</td>');
        if ($text) {
          $record[] = $text;
        }
      }
    } while ($trToken != '</tr>');

    assert(count($record) <= 4);
    if ($headerRow) {
      $headerRow = false;
      continue;
    }

    if (!count($record)) {
      continue;
    }

    $modelType = (count($record) >= 2) ? $record[1] : 'I';
    $modelNo = (count($record) >= 3) ? $record[2] : 1;
    $restr = (count($record) >= 4) ? $record[3] : '';
    $lexems = parseWordField($record[0], $modelType, $modelNo, $restr);
    $lexems = fixLexems($lexems);
    foreach($lexems as $l) {
      validateLexem($l);
      saveLexemWithExceptions($l);
    }
    $numLines++;
    if ($numLines % 5000 == 0) {
      $runTime = debug_getRunningTimeInMillis() / 1000;
      print $numLines . " lines, " . $numLines / $runTime . " lines/sec\n";
    }
  }
}

$runTime = debug_getRunningTimeInMillis() / 1000;
print $numLines . " lines, " . $numLines / $runTime . " lines/sec\n";

/*************************************************************************/

function readAndFormatFileL($fileName) {
  $fp = fopen($fileName, 'r');
  $data = fread($fp, 20000000);
  fclose($fp);
  $data = mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1');

  $data = str_replace(array("\n", "\r", '&#258;', '&#259;', '&#350;',
                            '&#354;', '&#355;', '&#269;'),
                      array(' ', ' ', 'Ă', 'ă', 'Ș',
                            'Ț', 'ț', 'č'),
                      $data);
  $data = preg_replace('/ +/', ' ', $data);
  return $data;
}

function validateLexem($l) {
  if (!text_validateAlphabet($l->form, LEGAL_LEXEM_CHARACTERS)) {
    die("Illegal characters in form {$l->form}\n");
  }
  if (!in_array($l->modelType, $GLOBALS['modelTypes'])) {
    die("Unknown model type {$l->modelType} for word {$l->form}\n");
  }
  if (!text_validateAlphabet($l->modelNumber, '0123456789')) {
    die("Unknown model number {$l->modelNumber} for word {$l->form}\n");
  }
  if (!text_validateAlphabet($l->restriction, 'SPUIT')) {
    die("Unknown restriction {$l->restriction} for word {$l->form}\n");
  }
}

// Returns an array of lexems
function parseWordField($word, $modelType, $modelNo, $restr) {
  $word = trim($word);

  // Look for a slash not included in brackets
  $len = mb_strlen($word);
  $parCount = 0;
  $i = 0;
  $found = false;
  while (($i < $len) && !$found) {
    $c = text_getCharAt($word, $i);
    if ($c == '[' || $c == '(') {
      $parCount++;
    } else if ($c == ']' || $c == ')') {
      $parCount--;
    }
    
    if ($c == '/' && !$parCount) {
      $found = true;
    } else {
      $i++;
    }
  }
  if ($found) {
    $r1 = parseWordField(mb_substr($word, 0, $i), $modelType,
                         $modelNo, $restr);
    $r2 = parseWordField(mb_substr($word, $i + 1), $modelType,
                         $modelNo, $restr);
    return array_merge($r1, $r2);
  }

  if (text_endsWith($word, ']')) {
    $pos = mb_strrpos($word, '[');
    assert ($pos !== false);
    $extra = mb_substr($word, $pos);
    $results = parseWordField(mb_substr($word, 0, $pos),
                              $modelType, $modelNo, $restr);
    assert(count($results));
    appendExtra($results[count($results) - 1], $extra);
    return $results;
  }

  if (text_endsWith($word, ')')) {
    $pos = mb_strrpos($word, '(');
    assert ($pos !== false);
    $extra = mb_substr($word, $pos);
    $results = parseWordField(mb_substr($word, 0, $pos),
                              $modelType, $modelNo, $restr);
    assert(count($results));

    // See if $extra contains a model number. If so, use it on the last model.
    list($modelType, $modelNo, $restr) = parseModel($extra);
    if ($modelType && $modelNo) {
      $results[count($results) - 1]->modelType = $modelType;
      $results[count($results) - 1]->modelNumber = $modelNo;
      $results[count($results) - 1]->restriction = $restr;
    }
    appendExtra($results[count($results) - 1], $extra);

    // If $extra dictates a part of speech, apply it to all the lexems
    if (text_contains($extra, 's.f.inv.') ||
        text_contains($extra, 's.f. în expr.') ||
        text_contains($extra, 's.m.inv.') ||
        text_contains($extra, 's.n.inv.') ||
        text_contains($extra, 'adj.inv.') ||
        text_contains($extra, 'adv.') ||
        text_contains($extra, 'conj.') ||
        text_contains($extra, 'prep.') ||
        text_contains($extra, 'interj.')) {
      foreach ($results as $l) {
        $l->modelType = 'I';
        $l->modelNumber = '1';
        $l->restriction = '';
      }
    }

    return $results;
  }

  $parts = split(',', $word);
  if (count($parts) >= 2) {
    $results = array();
    foreach ($parts as $part) {
      $results = array_merge($results,  parseWordField($part, $modelType,
                                                       $modelNo, $restr));
    }
    return $results;
  }

  $extra = text_contains($word, '-') ? $word : '';
  $word = str_replace('-', '', $word);

  $len = mb_strlen($word);
  $found = false;
  for ($i = 0; $i < $len && !$found; $i++) {
    $c = text_getCharAt($word, $i);
    if (text_isLowercase($c)) {
      $found = true;
      $word = text_insert($word, "'", $i);
    }
  }
  $word = text_unicodeToLower($word);

  $l = Lexem::create($word, $modelType, $modelNo, $restr);
  appendExtra($l, $extra);
  $l->isLoc = true;
  return array($l);
}
    
/**
 * Returns a list containing the next token and the new stream position.
 * If we reach the end of the file, the token is empty.
 * Tokens are opening tags (without the attributes), closing tags or text.
 **/
function getNextToken($pos) {
  global $data;
  global $dataLen;

  while ($pos < $dataLen && ctype_space($data[$pos])) {
    $pos++;
  }
  if ($pos >= $dataLen) {
    return array('', $dataLen);
  }

  $result = '';
  // If we hit a '<' sign, parse tag.
  if ($data[$pos] == '<') {
    do {
      $result .= $data[$pos];
      $done = text_startsWith($result, '<!--')
	? text_endsWith($result, '-->')
	: ($data[$pos] == '>');
      $pos++;
    } while (!$done);
    // Strip the attributes
    $tagEnd = 1;
    while (!ctype_space($result[$tagEnd]) && $result[$tagEnd] != '>') {
      $tagEnd++;
    }
    $result = substr($result, 0, $tagEnd) . '>';
    return array($result, $pos);
  }

  // Parse text to the next '<' sign or EOF.
  while ($pos < $dataLen && $data[$pos] != '<') {
    $result .= $data[$pos];
    $pos++;
  }
  return array(trim($result), $pos);
}

function parseModel($s) {
  $matches = array();
  $result = preg_match('/([A-Z]+)([0-9]+)([SPUIT]*)/', $s, $matches);
  if ($result) {
    assert(in_array($matches[1], $GLOBALS['modelTypes']));
    return array($matches[1], $matches[2], $matches[3]);
  }
  return array(null, null, null);
}

// Fixes situations like PRESPAPIeR [-pier] / [-pi-e], where we end up with
// a second empty lexem. Instead, we should have one lexem with two
// pronunciation rules.
function fixLexems($lexems) {
  $results = array();
  foreach ($lexems as $l) {
    if ($l->form) {
      $results[] = $l;
    } else {
      $prev = $results[count($results) - 1];
      appendExtra($prev, '/ ' . $l->extra);
    }
  }
  return $results;
}

function appendExtra(&$l, $extra) {
  $l->extra = $l->extra ? ($l->extra . ' ' . $extra) : $extra;
}

function saveLexemWithExceptions($l) {
  $exceptions = array(
                      array('administratoare', 'MF', '66', ''),
                      array('ăllalt', 'P', '23', ''),
                      array('ălalalt', 'P', '23', ''),
                      array('beși', 'V', '319', ''),
                      array('câtea', 'F', '151', ''),
                      array('celalalt', 'P', '31', ''),
                      array('cellalt', 'P', '31', ''),
                      array('greață', 'F', '38', ''),
                      array('istalalt', 'P', '63', ''),
                      array('istălalt', 'P', '63', ''),
                      array('înși', 'MF', '4', ''),
                      array('mielea', 'P', '74', ''),
                      array('oară', 'MF', '28', ''),
                      array('oare', 'MF', '66', ''),
                      array('scrabble', 'N', '76', ''),
                      array('voi', 'N', '67', 'pron.'),
                      );
  foreach ($exceptions as $e) {
    if ($l->unaccented == $e[0] &&
        $l->modelType == $e[1] &&
        $l->modelNumber == $e[2] &&
        (!$e[3] || text_contains($l->extra, $e[3]))) {
      return;
    }
  }

  $forced = array(
                  array("at'ât", 'I', "at'âta", 'P', '20', ''),
                  array("fi", 'I', '', 'V', '339', ''),
                  array("la", 'I', '', 'VT', '99', ''),
                  array("mult", 'I', '', 'P', '76', ''),
                  array("r'umpe", 'VT', '', 'VT', "657'", ''),
                  array("cor'umpe", 'VT', '', 'VT', "657'", ''),
                  array("întrer'umpe", 'VT', '', 'VT', "657'", ''),
                  array("ir'umpe", 'V', '', 'V', "657'", ''),
                  array("tot", 'I', '', 'P', '98', ''),
                  );
  foreach ($forced as $f) {
    if ($l->form == $f[0] && $l->modelType == $f[1]) {
      if ($f[2]) {
        $l->form = $f[2];
        $l->unaccented = str_replace("'", '', $l->form);
        $l->reverse = text_reverse($l->unaccented);
      }
      $l->modelType = $f[3];
      $l->modelNumber = $f[4];
      $l->restriction = $f[5];
    }
  }

  $l->save();
}

?>
