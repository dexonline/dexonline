<?
require_once("../../phplib/util.php");
ini_set('max_execution_time', '3600');
assert_options(ASSERT_BAIL, 1);
debug_off();
list($verbose, $fileName) = parseArguments();
$radu = User::loadByNick('raduborza');
$mdnSrc = Source::load(21);
$lines = file($fileName);
$linesSeen = 0;
$skipped = 0;

$existing = loadExistingMapByLexicon();
//$existing = array();

print "Importing " . count($lines) . " lines\n";
foreach ($lines as $count => $line) {
  $line = trim($line);
  $line = str_replace(array(chr(0x96), chr(0x84), chr(0x93), chr(228),
			    chr(0x0d) . ' ()', chr(146), chr(246), chr(160),
			    chr(180), chr(239), chr(251), chr(252),
			    chr(244), chr(234), chr(224), chr(145),
			    chr(235), chr(199), chr(241), chr(154),
			    chr(230), chr(201), chr(196), chr(171),
			    chr(187), chr(211), chr(167), chr(151),
			    chr(249), chr(0x0d) . chr(0) . ' ()', chr(214),
			    '\\', chr(168), '&lt;', ' I.@ ',
			    ' S. M.@ ', ' S. F.@ ', ' S. N.@ ',
			    ' VB.@ ', ' LOC@ ', '; -~A@ '),
		      array('-', '"', '"', ':a',
			    '', "'", ':o', '',
			    "'", ':i', '^u', ':u',
			    '^o', '^e', '`a', ',',
			    ':e', ',c', '~n', '',
			    ',T', "'E", ':A', '\\201c',
			    '\\201e', "'O", '\\00a7', '\\00b6',
			    '`u', '', ':O',
			    ',', '\\00a8', '< ', '@ I. ',
			    '@ $s. m.$ ', '@ $s. f.$ ', '@ $s. n.$ ',
			    '@ $vb.$ ', '@ $loc.$ ', ', -~A@ '),
		      $line);
  verify_alpha($line, $count);
  $line = text_internalizeDefinition($line);
  $d = new Definition();
  $d->userId = $radu->id;
  $d->sourceId = $mdnSrc->id;
  $d->internalRep = $line;
  $d->htmlRep = text_htmlize($line);
  $d->lexicon = text_extractLexicon($d);
  $d->status = ST_ACTIVE;

  if (array_key_exists($d->lexicon, $existing)) {
    $skipped++;
  } else {
    // Handle some special cases where the lexicon needs to be adjusted.
    if (preg_match("/^@[^@,]+ /", $d->internalRep)) {
      $pos = strpos($d->internalRep, '@', 1);
      $text = substr($d->internalRep, 1, $pos - 1);
      $parts = split(' ', $text);
      assert(count($parts) >= 2);
      if ($parts[count($parts) - 1] == 'II.') {
	$d->lexicon = text_internalizeWordName($parts[0]);
	$d->status = ST_PENDING;
      } else if (count($parts) == 2 && text_endsWith($parts[1], '/')) {
	// Use the first part only, because the second one is the pronunciation
	$d->lexicon = text_internalizeWordName($parts[0]);	
	//print "Using [{$d->lexicon}] for " . mb_substr($d->internalRep, 0, 50) . "\n";
      } else {
	$d->status = ST_PENDING;
      }
    }

    if ($d->lexicon) {
      $lexems = Lexem::loadByUnaccented($d->lexicon);
      if (!count($lexems)) {
	$lexem = Lexem::create($d->lexicon, 'T', '1', '');
	$lexem->save();
	$lexem->id = db_getLastInsertedId();
	$lexem->regenerateParadigm();
	$lexems[] = $lexem;
      }
      $d->save();
      $d->id = db_getLastInsertedId();
      foreach ($lexems as $l) {
	$ldm = LexemDefinitionMap::create($l->id, $d->id);
	$ldm->save();
      }
    } else {
      print "Skipping [{$d->internalRep}]\n";
    }
  }
  
  if (++$linesSeen % 1000 == 0) {
    print "$linesSeen lines seen.\n";
  }
}
print "Skipped $skipped existing definitions\n";

/***************************************************************************/

function parseArguments() {
  global $argv;
  $verbose = false;
  $fileName = '/tmp/dn.online';
  for ($i = 1; $i < count($argv); $i++) {
    $arg = $argv[$i];
    if ($arg == "-v") {
      $verbose = true;
    } else if ($arg == '-f') {
      $i++;
      $fileName = $argv[$i];
    } else {
      os_errorAndExit("Unknown flag: $arg");
    }
  }
  return array($verbose, $fileName);
}

function verify_alpha($s, $count) {
  $len = strlen($s);
  for ($i = 0; $i < $len; $i++) {
    $o = ord($s[$i]);
    if ($o < 32 || $o >= 128) {
      die ("Illegal character $o at position $i on line $count [$s]\n");
    }
  }
}

function loadExistingMapByLexicon() {
  $result = array();
  $query = "select * from Definition where SourceId = 21 and Status = 0";
  $defs = Definition::populateFromDbResult(mysql_query($query));
  foreach ($defs as $def) {
    $l = $def->lexicon;
    if (array_key_exists($l, $result)) {
      $result[$l][] = $def;
    } else {
      $result[$l] = array($def);
    }
  }
  return $result;
}

?>
