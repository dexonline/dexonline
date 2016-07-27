<?php
require_once("../../phplib/util.php");
set_time_limit(0);

// If no GET arguments are set, print usage and return.
if (count($_GET) == 0) {
  SmartyWrap::display('deprecated/update3Instructions.tpl');
  return;
}

util_enforceGzipEncoding();

header('Content-type: text/xml');
$export = util_getRequestParameter('export');
$timestamp = util_getRequestIntParameter('timestamp');
$version = util_getRequestParameterWithDefault('version', '3.0');

if ($export && util_isDesktopBrowser() && !session_getUser()) {
  SmartyWrap::display('bits/updateError.tpl');
  exit();
}

if ($export == 'sources') {
  SmartyWrap::assign('sources', Model::factory('Source')->find_many());
  SmartyWrap::displayWithoutSkin('xml/update3Sources.tpl');
} else if ($export == 'inflections') {
  SmartyWrap::assign('inflections', Model::factory('Inflection')->order_by_asc('id')->find_many());
  SmartyWrap::displayWithoutSkin('xml/update3Inflections.tpl');
} else if ($export == 'abbrev') {
  SmartyWrap::assign('abbrev', AdminStringUtil::loadRawAbbreviations());
  SmartyWrap::displayWithoutSkin('xml/update3Abbrev.tpl');
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
    SmartyWrap::displayWithoutSkin('xml/update3Definitions.tpl');
  }

  print "</Definitions>\n";
} else if ($export == 'lexems') {
  $lexemDbResult = db_execute("select * from Lexem where modDate >= '{$timestamp}' order by modDate, id");
  print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  print "<Lexems>\n";
  print "<NumResults>" . $lexemDbResult->rowCount() . "</NumResults>\n";
  foreach ($lexemDbResult as $dbRow) {
    $lexem = Model::factory('Lexem')->create($dbRow);
    SmartyWrap::assign('lexem', $lexem);
    SmartyWrap::displayWithoutSkin('xml/update3Lexems.tpl');
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
