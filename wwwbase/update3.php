<?php
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
$version = util_getRequestParameterWithDefault('version', '3.0');

if ($export && util_isDesktopBrowser() && !session_getUser()) {
  smarty_displayCommonPageWithSkin('updateError.ihtml');
  exit();
}

if ($export == 'sources') {
  smarty_assign('sources', db_find(new Source(), '1'));
  smarty_displayWithoutSkin('common/update3Sources.ihtml');
} else if ($export == 'inflections') {
  smarty_assign('inflections', db_find(new Inflection(), '1 order by id'));
  smarty_displayWithoutSkin('common/update3Inflections.ihtml');
} else if ($export == 'abbrev') {
  smarty_assign('abbrev', AdminStringUtil::loadRawAbbreviations());
  smarty_displayWithoutSkin('common/update3Abbrev.ihtml');
} else if ($export == 'definitions') {
  userCache_init();
  $d = new Definition();
  $statusClause = $timestamp ? '' : ' and status = 0';
  $defDbResult = db_execute("select * from Definition where modDate >= '$timestamp' and sourceId in (select id from Source where canDistribute) " .
                            "$statusClause order by modDate, id"); // 
  $lexemDbResult = db_execute("select Definition.id, lexemId from Definition force index(modDate), LexemDefinitionMap " .
                              "where Definition.id = definitionId and Definition.modDate >= {$timestamp} " .
                              "and sourceId in (select id from Source where canDistribute) " .
                              "{$statusClause} order by Definition.modDate, Definition.id");
  $currentLexem = fetchNextLexemTuple();
  print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  print "<Definitions>\n";
  print "  <NumResults>" . $defDbResult->RowCount() . "</NumResults>\n";

  while (!$defDbResult->EOF) {
    $def = new Definition();
    $def->set($defDbResult->fields);
    $defDbResult->MoveNext();
    $def->internalRep = AdminStringUtil::xmlizeRequired($def->internalRep);

    $lexemIds = array();
    while ($currentLexem && $currentLexem[0] == $def->id) {
      $lexemIds[] = $currentLexem[1];
      $currentLexem = fetchNextLexemTuple();
    }
    prepareDefForVersion($def);
    smarty_assign('def', $def);
    smarty_assign('lexemIds', $lexemIds);
    smarty_assign('user', userCache_get($def->userId));
    smarty_displayWithoutSkin('common/update3Definitions.ihtml');
  }

  print "</Definitions>\n";
} else if ($export == 'lexems') {
  $lexemDbResult = db_execute("select * from Lexem where modDate >= '{$timestamp}' order by modDate, id");
  print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  print "<Lexems>\n";
  print "<NumResults>" . $lexemDbResult->RowCount() . "</NumResults>\n";
  while (!$lexemDbResult->EOF) {
    $lexem = new Lexem();
    $lexem->set($lexemDbResult->fields);
    $lexemDbResult->MoveNext();

    smarty_assign('ifs', InflectedForm::loadByLexemId($lexem->id));
    smarty_assign('lexem', $lexem);
    smarty_displayWithoutSkin('common/update3Lexems.ihtml');
  }
  print "</Lexems>\n";
}

/****************************************************************************/

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

function fetchNextLexemTuple() {
  global $lexemDbResult;

  if ($lexemDbResult->EOF) {
    return null;
  }
  $result = array($lexemDbResult->fields[0], $lexemDbResult->fields[1]);
  $lexemDbResult->MoveNext();
  return $result;
}

function prepareDefForVersion(&$def) {
  global $version;
  if ($version == '3.0') {
    $def->internalRep = preg_replace('/([^&])#/', '\1', $def->internalRep);
  }
}

?>
