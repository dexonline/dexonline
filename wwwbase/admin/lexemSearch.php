<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$form = util_getRequestParameter('form');
$sourceId = util_getRequestParameter('source');
$loc = util_getRequestParameter('loc');
$paradigm = util_getRequestParameter('paradigm');
$structStatus = util_getRequestParameter('structStatus');
$nick = util_getRequestParameter('nick');
$searchButton = util_getRequestParameter('searchButton');

if (!$searchButton) {
  util_redirect('index.php');
}

$where = array();
$joinNeeded = false;

// Process the $form argument
$form = StringUtil::cleanupQuery($form);
list ($hasDiacritics, $hasRegexp, $ignored) = StringUtil::analyzeQuery($form);
$field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
if ($hasRegexp) {
  $fieldValue = StringUtil::dexRegexpToMysqlRegexp($form);
} else {
  $fieldValue = "= '{$form}'";
}
$where[] = "{$field} {$fieldValue}";

// Process the $sourceId argument
if ($sourceId) {
  $joinNeeded = true;
  $where[] = "sourceId = {$sourceId}";
}

// Process the $loc argument
switch ($loc) {
  case 0: $where[] = "not isLoc"; break;
  case 1: $where[] = "isLoc"; break;
}

// Process the $paradigm argument
switch ($paradigm) {
  case 0: $where[] = "modelType = 'T'"; break;
  case 1: $where[] = "modelType != 'T'"; break;
}

// Process the $structStatus argument
if ($structStatus) {
  $where[] = "structStatus = {$structStatus}";
}

// Process the $nick argument
if ($nick) {
  $user = User::get_by_nick($nick);
  if ($user) {
    $joinNeeded = true;
    $where[] = "userId = {$user->id}";
  }
}

$tables = $joinNeeded
  ? "Lexem l join LexemDefinitionMap ldm on l.id = ldm.lexemId join Definition d on ldm.definitionId = d.id"
  : "Lexem l";

$query = sprintf("select l.* from %s where %s order by formNoAccent limit 10000",
                 $tables, implode(' and ', $where));
$lexems = Model::factory('Lexem')->raw_query($query, null)->find_many();

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('sectionTitle', 'Căutare lexeme');
SmartyWrap::assign('sectionCount', count($lexems));
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/lexemList.ihtml');

?>
