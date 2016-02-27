<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT | PRIV_STRUCT);
util_assertNotMirror();

$form = util_getRequestParameter('form');
$sourceId = util_getRequestParameter('source');
$loc = util_getRequestParameterWithDefault('loc', 2);
$paradigm = util_getRequestParameterWithDefault('paradigm', 2);
$structStatus = util_getRequestParameter('structStatus');
$structuristId = util_getRequestParameter('structuristId');
$nick = util_getRequestParameter('nick');

$where = array();
$joins = array();

// Process the $form argument
$form = StringUtil::cleanupQuery($form);
if ($form) {
  list ($hasDiacritics, $hasRegexp, $ignored) = StringUtil::analyzeQuery($form);
  $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
  if ($hasRegexp) {
    $fieldValue = StringUtil::dexRegexpToMysqlRegexp($form);
  } else {
    $fieldValue = "= '{$form}'";
  }
  $where[] = "{$field} {$fieldValue}";
}

// Process the $sourceId argument
if ($sourceId) {
  $joins['definition'] = true;
  $where[] = "sourceId = {$sourceId}";
}

// Process the $loc argument
switch ($loc) {
  case 0:
    $joins['lexemModel'] = true;
    $where[] = "not lm.isLoc";
    break;
  case 1:
    $joins['lexemModel'] = true;
    $where[] = "lm.isLoc";
    break;
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

// Process the $structStatus argument
if ($structuristId > 0) {
  $where[] = "structuristId = {$structuristId}";
} else if ($structuristId == -1) {
  $where[] = "structuristId = 0 or structuristId is null";
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
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/lexemSearch.tpl');

?>
