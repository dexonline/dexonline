<?
require_once('../phplib/util.php');

// Allow this script to run indefinitely long
set_time_limit(0);

// If no jadexVersion argument is set, print usage and return.
$jadexVersion = util_getRequestParameter('jadexVersion');
if ( $jadexVersion != '1.0' || empty($_GET['timestamp']) ) {
  usage();
  return;
}

$acceptEncoding = isset($_SERVER['HTTP_ACCEPT_ENCODING'])
  ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';

if ( strstr($acceptEncoding, 'gzip') === FALSE ) {
  header('HTTP/1.0 403 Forbidden');
  return;
}

header('Content-Type: text/plain;charset=UTF-8');

$timestamp = util_getRequestIntParameter('timestamp');
$defDbResult = Definition::loadByMinModDate($timestamp);
$lexemDbResult = Lexem::loadNamesByMinModDate($timestamp);
$sources = Source::findAll('');
userCache_init();
$currentLexem = array(0, ''); // Force loading a lexem on the next comparison.

// marker
print "<jadexUpdate version='1.0'>\n";
// timestamp
print time() . "\n";
// total results expected
print mysql_num_rows($defDbResult) . "\n";
// sources
foreach ( $sources as $source ) {
	// marker
	print "<source>\n";
	print $source->id . "\n";
	print $source->shortName . "\n";
	print $source->name . "\n";
	print $source->author. "\n";
	print $source->publisher . "\n";
	print $source->year . "\n";
	// marker
	print "</source>\n";
}

while ( $dbRow = mysql_fetch_assoc($defDbResult) ) {
	$def = Definition::createFromDbRow($dbRow);
	$def->internalRep = text_xmlizeRequired($def->internalRep);

	$lexemNames = array();
	$lexemLatinNames = array();
	while ( merge_compare($def, $currentLexem) < 0 ) {
		$currentLexem = mysql_fetch_row($lexemDbResult);
	}

	while (merge_compare($def, $currentLexem) == 0) {
		$lexemNames[] = $currentLexem[1];
		$lexemLatinNames[] = text_unicodeToLatin($currentLexem[1]);
		$currentLexem = mysql_fetch_row($lexemDbResult);
		// marker
		print "<entry>\n";
		print $def->id . "\n";
		print $lexemLatinNames[0] . "\n";
		print $lexemNames[0] . "\n";
		print $def->sourceId . "\n";
		print userCache_get($def->userId)->nick . "\n";
		print $def->modDate . "\n";
		// definition can span multiple line, so read until marker
		print $def->internalRep . "\n";
		// marker
		print "</entry>\n";
	}
}

// end marker
print "</jadexUpdate>";

function userCache_init() {
  $GLOBALS['USER'] = array();
}

function userCache_get($key) {
  if (array_key_exists($key, $GLOBALS['USER'])) {
    return $GLOBALS['USER'][$key];
  }

  $user = User::get("id = $key");
  $GLOBALS['USER'][$key] = $user;
  return $user;
}

function merge_compare(&$def, &$lexem) {
  if (!$lexem) {
    return 1; // We're at the end of the lexem result set
  } else if ($def->id > $lexem[0]) {
    return -1;
  } else if ($def->id < $lexem[0]) {
    return 1;
  } else {
    return 0;
  }
}

function usage() {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>DEX online - jadex_update.php</title>
  </head>

  <body>
    <p>This page is meant for automated use by the <a href="http://www.federicomestrone.com/jadex/">JaDEX application</a> only!</p>
  </body>
</html>
<? } ?>
