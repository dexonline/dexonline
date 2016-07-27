<?php
require_once("../../phplib/util.php");

// Allow this script to run indefinitely long
set_time_limit(0);

// If no GET arguments are set, print usage and return.
if (count($_GET) == 0) {
  SmartyWrap::display('deprecated/updateInstructions.tpl');
  return;
}

$acceptEncoding = isset($_SERVER['HTTP_ACCEPT_ENCODING'])
  ? $_SERVER['HTTP_ACCEPT_ENCODING'] : "";

if (strstr($acceptEncoding, "gzip") === FALSE) {
  header("HTTP/1.0 403 Forbidden");
  return;
}

$timestamp = util_getRequestIntParameter('timestamp');
$version = util_getRequestParameterWithDefault('version', '1.0');

if ($timestamp !== null && util_isDesktopBrowser() && !session_getUser()) {
  SmartyWrap::display('bits/updateError.tpl');
  exit();
}

header('Content-type: text/xml');

$defDbResult = db_execute("select * from Definition where status = " . Definition::ST_ACTIVE . " and sourceId in (select id from Source where canDistribute) " .
                          "and modDate >= '$timestamp' order by modDate, id");
$lexemDbResult = Lexem::loadNamesByMinModDate($timestamp);
$sourceMap = createSourceMap();
userCache_init();
$currentLexem = array(0, ''); // Force loading a lexem on the next comparison.

print "<!DOCTYPE dict [\n";
print "  <!ENTITY diams \"&#x2666;\">\n";
print "  <!ENTITY loz \"&#x25ca;\">\n";
print "  <!ENTITY rsquo \"&#x2019;\">\n";
print "]>\n";
print "<Dictionary>\n";
print "  <Timestamp>" . time() . "</Timestamp>\n";
if ($version == '1.0') {
  print "    <NumResults>" . $defDbResult->rowCount() . "</NumResults>\n";
}

foreach ($defDbResult as $row) {
  fetchNextRow($row);
  SmartyWrap::assign('version', $version);
  SmartyWrap::assign('includeNameWithDiacritics', hasFlag('a'));
  SmartyWrap::displayWithoutSkin('xml/update.tpl');
}

print "</Dictionary>\n";
return;



function createSourceMap() {
  $sourceMap = array();
  $sources = Model::factory('Source')->find_many();
  foreach ($sources as $source) {
    $sourceMap[$source->id] = $source;
  }
  return $sourceMap;
}

function userCache_init() {
  $GLOBALS['USER'] = array();
}

function userCache_get($key) {
  if (array_key_exists($key, $GLOBALS['USER'])) {
    return $GLOBALS['USER'][$key];
  }

  $user = User::get_by_id($key);
  $GLOBALS['USER'][$key] = $user;
  return $user;
}

function hasFlag($f) {
  return (isset($_GET['flags']) &&
	  strstr($_GET['flags'], $f) !== FALSE);
}

function fetchNextRow($row) {
  global $lexemDbResult;
  global $sourceMap;
  global $currentLexem;

  $def = Model::factory('Definition')->create($row);
  $def->internalRep = AdminStringUtil::xmlizeRequired($def->internalRep);
  if (hasFlag('d')) {
    $def->internalRep = AdminStringUtil::xmlizeOptional($def->internalRep);
  }

  $lexemNames = array();
  $lexemLatinNames = array();
  while (merge_compare($def, $currentLexem) < 0) {
    $currentLexem = $lexemDbResult->fetch();
  }

  while (merge_compare($def, $currentLexem) == 0) {
    $lexemNames[] = $currentLexem[1];
    $lexemLatinNames[] = StringUtil::unicodeToLatin($currentLexem[1]);
    $currentLexem = $lexemDbResult->fetch();
  }

  SmartyWrap::assign('def', $def);
  SmartyWrap::assign('lexemNames', $lexemNames);
  SmartyWrap::assign('lexemLatinNames', $lexemLatinNames);
  SmartyWrap::assign('source', $sourceMap[$def->sourceId]);
  SmartyWrap::assign('user', userCache_get($def->userId));
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

?>
