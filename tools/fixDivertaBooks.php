<?

require_once('../phplib/util.php');
require_once('../phplib/ads/adsModule.php');
require_once('../phplib/ads/diverta/divertaAdsModule.php');

$opts = getopt('s:');
if (count($opts) != 1) {
  print "Usage: fixDivertaBooks -s <start-id>\n";
  exit;
}

// Resolve some ambiguities automatically. List the form that is alphabetically first and specify what to return
$PREFERRED_FORMS = array('a' => 'a',                 // not ă â
                         'al' => 'al',               // not ăl
                         'carte' => 'carte',         // not cârțe
                         'cartea' => 'cartea',       // not cârtea
                         'fara' => 'fără',           // not fara fară
                         'in' => 'în',               // not in
                         'la' => 'la',               // not lă
                         'mai' => 'mai',             // not măi mâi
                         'mare' => 'mare',           // not măre mâre
                         'marea' => 'marea',         // not mărea
                         's' => 's',                 // not ș
                         'sa' => 'să',               // not sa șa
                         'si' => 'și',               // not si
                         'teste' => 'teste',         // not țeste
                         'ti' => 'ți',               // not ti
                         'timp' => 'timp',           // not țimp
                         'top' => 'top',             // not țop
);

$books = db_find(new DivertaBook(), "id >= {$opts['s']} order by id limit 30");
foreach ($books as $book) {
  print "Loaded: {$book->id} [{$book->title}]\n";
  $origTitle = $book->title;

  // Preliminary stuff
  $book->title = trim($book->title);
  if (text_endsWith($book->title, ', ***')) {
    $book->title = substr($book->title, 0, -5);
  }
  switch ($book->sku) {
  case 'YDA00965': $book->title = 'Dicționar vizual spaniol-român'; break;
  case 'YHG00310': $book->title = '77 de rețete celebre și poveștile lor'; break;
  case 'YHU02030': $book->title = 'Zen aici și acum'; break;
  case 'YCV00945': $book->title = 'Bărbatul manipulator'; break;
  case 'YPS00372': $book->title = 'As în vânzări. Tactici de negociere'; break;
  case 'YVI00135': $book->title = 'Prințesa Masako. Prizoniera Tronului Crizantemei. Povestea adevărată și tragică a prințesei Japoniei'; break;
  case 'YPS00374': $book->title = 'Cum se formează lideri ca Jack Welch'; break;
  case 'YCV00675': $book->title = 'Ce-ar fi dacă ne-am inventa propria viață?'; break;
  case 'YAL01835': $book->title = 'Diana, Prințesa Inimilor'; break;
  case 'YMP00064': $book->title = 'Album „Suflet Candriu de Papugiu”, Volumul II'; break;
  case 'YPO02664': $book->title = 'Cetateanul Kane, „Romanul” unui film'; break;
  case 'YMP00065': $book->title = 'Album „Suflet Candriu de Papugiu”, Volumele I si II'; break;
  case 'YCV00767': $book->title = 'Un an fara „Made in China”'; break;
  case 'YNDC19342': $book->title = 'I\'ll be Watching You, Inside the Police, 1980-83'; break;
  case 'YADV00040': $book->title = 'Enigma „Profesor Rebegea”'; break;
  case '': $book->title = ''; break;
  case '': $book->title = ''; break;
  case '': $book->title = ''; break;
  }
  $book->title = str_replace(array('`', '…', '–', '’', ' .', ' ,', ' :', ' ?', ' !', 'intr-'),
                             array("'", '...', '-', "'", '.', ',', ':', '?', '!', 'într-'), $book->title);
  print "Prelim: {$book->id} [{$book->title}]\n";

  // Extract words
  $newTitle = '';
  $inWord = false;
  $word = '';
  for ($i = 0; $i < mb_strlen($book->title); $i++) {
    $c = text_getCharAt($book->title, $i);
    if (text_isUnicodeLetter($c)) {
      $word .= $c;
      $inWord = true;
    } else {
      if ($inWord) {
        $newTitle .= matchCase(suggest($word), $word);
      }
      $word = '';
      $inWord = false;
      $newTitle .= $c;
    }
  }
  if ($inWord) {
    $newTitle .= matchCase(suggest($word), $word);
  }
  $book->title = $newTitle;
  print "Final:  {$book->id} [{$book->title}]\n";
  if ($book->title != $origTitle) {
    $response = kbdInput("Save? [Y/n]", array('y', 'n', ''));
    if ($response != 'n') {
      $book->save();
    }
  }
}

function suggest($word) {
  global $PREFERRED_FORMS;
  $forms = db_getArray(db_execute("select distinct formNoAccent from InflectedForm where formUtf8General = '{$word}' order by formNoAccent"));
  if (!count($forms)) {
    return $word;
  } else if (count($forms) == 1) {
    return $forms[0];
  } else if (array_key_exists($forms[0], $PREFERRED_FORMS)) {
    return $PREFERRED_FORMS[$forms[0]];
  } else {
    return choice($word, $forms);
  }
}

/**
 * Change the case of letters in $word to match those in $like
 **/
function matchCase($word, $like) {
  $len = min(mb_strlen($word), mb_strlen($like));
  for ($i = 0; $i < $len; $i++) {
    $cWord = text_getCharAt($word, $i);
    $cLike = text_getCharAt($like, $i);
    if (text_isUppercase($cLike)) {
      $word = mb_substr($word, 0, $i) . text_unicodeToUpper($cWord) . mb_substr($word, $i + 1);
    } else {
      $word = mb_substr($word, 0, $i) . text_unicodeToLower($cWord) . mb_substr($word, $i + 1);
    }
  }
  return $word;
}

function choice($word, $forms) {
  $message = "<CR> {$word}    ";
  $choices = array('');
  foreach ($forms as $i => $form) {
    $message .= sprintf("[%d] %s    ", $i + 1, $form);
    $choices[] = $i + 1;
  }
  $choice = kbdInput($message, $choices);
  return ($choice == '') ? $word : $forms[$choice - 1];
}

function kbdInput($message, $choices) {
  do {
    print $message;
    $response = trim(fgets(STDIN));
  } while (!in_array($response, $choices));
  return $response;
}

?>
