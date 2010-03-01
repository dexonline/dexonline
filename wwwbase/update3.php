<?
require_once("../phplib/util.php");
set_time_limit(0);

// If no GET arguments are set, print usage and return.
if (count($_GET) == 0) {
  smarty_displayWithoutSkin('common/update3Instructions.ihtml');
  return;
}

util_enforceGzipEncoding();

header('Content-type: text/xml');
$export = util_getRequestParameter('export');
$timestamp = util_getRequestIntParameter('timestamp');

if ($export == 'sources') {
  smarty_assign('sources', Source::findAll(''));
  smarty_displayWithoutSkin('common/update3Sources.ihtml');
} else if ($export == 'inflections') {
  smarty_assign('inflections', Inflection::loadAll());
  smarty_displayWithoutSkin('common/update3Inflections.ihtml');
} else if ($export == 'definitions') {
  userCache_init();
  $defDbResult = db_getUpdate3Definitions($timestamp);
  $lexemDbResult = db_getUpdate3LexemIds($timestamp);
  $currentLexem = mysql_fetch_row($lexemDbResult);
  smarty_assign('numResults', mysql_num_rows($defDbResult));
  smarty_displayWithoutSkin('common/update3Definitions.ihtml');
} else if ($export == 'lexems') {
  $lexemDbResult = db_getUpdate3Lexems($timestamp);
  smarty_assign('numResults', mysql_num_rows($lexemDbResult));
  smarty_displayWithoutSkin('common/update3Lexems.ihtml');
}

/****************************************************************************/

function userCache_init() {
  $GLOBALS['USER'] = array();
}

function userCache_get($key) {
  if (array_key_exists($key, $GLOBALS['USER'])) {
    return $GLOBALS['USER'][$key];
  }

  $user = User::load($key);
  $GLOBALS['USER'][$key] = $user;
  return $user;
}

function fetchNextRow() {
  global $defDbResult;
  global $lexemDbResult;
  global $currentLexem;

  $dbRow = mysql_fetch_assoc($defDbResult);
  $def = Definition::createFromDbRow($dbRow);
  $def->internalRep = text_xmlizeRequired($def->internalRep);

  $lexemIds = array();
  while ($currentLexem && $currentLexem[0] == $def->id) {
    $lexemIds[] = $currentLexem[1];
    $currentLexem = mysql_fetch_row($lexemDbResult);
  }

  smarty_assign('def', $def);
  smarty_assign('lexemIds', $lexemIds);
  smarty_assign('user', userCache_get($def->userId));
}

function fetchNextLexemRow() {
  global $lexemDbResult;

  $dbRow = mysql_fetch_assoc($lexemDbResult);
  $lexem = Lexem::createFromDbRow($dbRow);

  smarty_assign('wordLists', WordList::loadByLexemId($lexem->id));
  smarty_assign('lexem', $lexem);
}

?>
