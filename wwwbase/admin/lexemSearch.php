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
$joins = array();

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
  $joins['definition'] = true;
  $where[] = "sourceId = {$sourceId}";
}

// Process the $loc argument
switch ($loc) {
  case 0: $where[] = "not l.isLoc"; break;
  case 1: $where[] = "l.isLoc"; break;
}

// Process the $paradigm argument
switch ($paradigm) {
  case 0:
    $joins['lexemModel'] = true;
    $where[] = "modelType = 'T'";
    break;
  case 1:
    $joins['lexemModel'] = true;
    $where[] = "modelType != 'T'";
    break;
}

// Process the $structStatus argument
if ($structStatus) {
  $where[] = "structStatus = {$structStatus}";
}

// Process the $nick argument
if ($nick) {
  $user = User::get_by_nick($nick);
  if ($user) {
    $joins['definition'] = true;
    $where[] = "userId = {$user->id}";
  }
}

// Assemble the query
$query = Model::factory('Lexem')
  ->table_alias('l')
  ->select('l.*')
  ->distinct()
  ->order_by_asc('formNoAccent')
  ->limit(10000);

// ... and joins
foreach ($joins as $join => $ignored) {
  switch ($join) {
    case 'definition':
      $query = $query->join('LexemDefinitionMap', 'l.id = ldm.lexemId', 'ldm')
        ->join('Definition', 'ldm.definitionId = d.id', 'd');
      break;
    case 'lexemModel':
      $query = $query->join('LexemModel', 'l.id = lm.lexemId', 'lm');
  }
}

// ... and where clauses
foreach ($where as $clause) {
  $query = $query->where_raw("({$clause})");
}

$lexems = $query->find_many();

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('sectionTitle', 'Căutare lexeme');
SmartyWrap::assign('sectionCount', count($lexems));
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/lexemList.ihtml');

?>
