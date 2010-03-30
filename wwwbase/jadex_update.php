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
$defDbResult = db_execute("select * from Definition where status = " . ST_ACTIVE . " and sourceId in (select id from Source where canDistribute) " .
                          "and modDate >= '$timestamp' order by modDate, id");
$lexemDbResult = Lexem::loadNamesByMinModDate($timestamp);
$sources = db_find(new Source(), '1');
userCache_init();
$currentLexem = array(0, ''); // Force loading a lexem on the next comparison.

// marker
print "<jadexUpdate version='1.0'>\n";
// timestamp
print time() . "\n";
// total results expected
print $lexemDbResult->RowCount() . "\n";
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

while (!$defDbResult->EOF) {
  $def = new Definition();
  $def->set($defDbResult->fields);
  $defDbResult->MoveNext();
  $def->internalRep = text_xmlizeRequired($def->internalRep);

  while ( merge_compare($def, $currentLexem) < 0 ) {
    $currentLexem = fetchNextLexem();
  }

  while (merge_compare($def, $currentLexem) == 0) {
    $lexemNames = $currentLexem[1];
    $lexemLatinNames = text_unicodeToLatin($currentLexem[1]);
    $currentLexem = fetchNextLexem();
    // marker
    print "<entry>\n";
    print $def->id . "\n";
    print $lexemLatinNames . "\n";
    print $lexemNames . "\n";
    print $def->sourceId . "\n";
    $user = userCache_get($def->userId);
    if ($user) {
      print "{$user->nick}\n";
    } else {
      print "anonim\n";
    }
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

function fetchNextLexem() {
  global $lexemDbResult;

  $result = $lexemDbResult->fields;
  $lexemDbResult->MoveNext();
  return $result;
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
