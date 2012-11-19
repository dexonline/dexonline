<?php
require_once("../../phplib/util.php");
set_time_limit(0);

// If no GET arguments are set, print usage and return.
if (count($_GET) == 0) {
  SmartyWrap::addCss('polar');
  SmartyWrap::displayWithoutSkin('common/update3Instructions.ihtml');
  return;
}

util_enforceGzipEncoding();

header('Content-type: text/xml');
$export = util_getRequestParameter('export');
$timestamp = util_getRequestIntParameter('timestamp');
$version = util_getRequestParameterWithDefault('version', '3.0');

if ($export && util_isDesktopBrowser() && !session_getUser()) {
  SmartyWrap::displayCommonPageWithSkin('updateError.ihtml');
  exit();
}

if ($export == 'sources') {
  SmartyWrap::assign('sources', Model::factory('Source')->find_many());
  SmartyWrap::displayWithoutSkin('common/update3Sources.ihtml');
} else if ($export == 'inflections') {
  SmartyWrap::assign('inflections', Model::factory('Inflection')->order_by_asc('id')->find_many());
  SmartyWrap::displayWithoutSkin('common/update3Inflections.ihtml');
} else if ($export == 'abbrev') {
  SmartyWrap::assign('abbrev', AdminStringUtil::loadRawAbbreviations());
  SmartyWrap::displayWithoutSkin('common/update3Abbrev.ihtml');
} else if ($export == 'definitions') {
  userCache_init();
  $statusClause = $timestamp ? '' : ' and status = 0';
  $defDbResult = db_execute("select * from Definition where modDate >= '$timestamp' and sourceId in (select id from Source where canDistribute) " .
                            "$statusClause order by modDate, id", PDO::FETCH_ASSOC); // 
  $lexemDbResult = db_execute("select Definition.id, lexemId from Definition force index(modDate), LexemDefinitionMap " .
                              "where Definition.id = definitionId and Definition.modDate >= {$timestamp} " .
                              "and sourceId in (select id from Source where canDistribute) " .
                              "{$statusClause} order by Definition.modDate, Definition.id", PDO::FETCH_NUM);
  $currentLexem = $lexemDbResult->fetch();
  print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  print "<Definitions>\n";
  print "  <NumResults>" . $defDbResult->rowCount() . "</NumResults>\n";

  foreach($defDbResult as $dbRow) {
    $def = Model::factory('Definition')->create($dbRow);
    $def->internalRep = AdminStringUtil::xmlizeRequired($def->internalRep);

    $lexemIds = array();
    while ($currentLexem && $currentLexem[0] == $def->id) {
      $lexemIds[] = $currentLexem[1];
      $currentLexem = $lexemDbResult->fetch();
    }
    prepareDefForVersion($def);
    SmartyWrap::assign('def', $def);
    SmartyWrap::assign('lexemIds', $lexemIds);
    SmartyWrap::assign('user', userCache_get($def->userId));
    SmartyWrap::displayWithoutSkin('common/update3Definitions.ihtml');
  }

  print "</Definitions>\n";
} else if ($export == 'lexems') {
  $lexemDbResult = db_execute("select * from Lexem where modDate >= '{$timestamp}' order by modDate, id");
  print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  print "<Lexems>\n";
  print "<NumResults>" . $lexemDbResult->rowCount() . "</NumResults>\n";
  foreach ($lexemDbResult as $dbRow) {
    $lexem = Model::factory('Lexem')->create($dbRow);
    SmartyWrap::assign('ifs', InflectedForm::loadByLexemId($lexem->id));
    SmartyWrap::assign('lexem', $lexem);
    SmartyWrap::displayWithoutSkin('common/update3Lexems.ihtml');
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

  $user = User::get_by_id($key);
  $GLOBALS['USER'][$key] = $user;
  return $user;
}

function prepareDefForVersion(&$def) {
  global $version;
  if ($version == '3.0') {
    $def->internalRep = preg_replace('/([^&])#/', '\1', $def->internalRep);
  }
}

?>
