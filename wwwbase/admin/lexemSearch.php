<?php
require_once("../../phplib/Core.php"); 
User::mustHave(User::PRIV_EDIT | User::PRIV_STRUCT);
Util::assertNotMirror();

$form = Request::get('form');
$sourceId = Request::get('source');
$loc = Request::get('loc', 2);
$paradigm = Request::get('paradigm', 2);
$structStatus = Request::get('structStatus');
$structuristId = Request::get('structuristId');
$nick = Request::get('nick');

$where = [];
$joins = [];

// Process the $form argument
if ($form) {
  list ($hasDiacritics, $hasRegexp, $ignored) = StringUtil::analyzeQuery($form);
  if ($hasRegexp) {
    $fieldValue = StringUtil::dexRegexpToMysqlRegexp($form);
  } else {
    $fieldValue = "= '{$form}'";
  }
  $where[] = "formNoAccent {$fieldValue}";
}

// Process the $sourceId argument
if ($sourceId) {
  $joins['entryLexem'] = true;
  $joins['definition'] = true;
  $where[] = "sourceId = {$sourceId}";
}

// Process the $loc argument
switch ($loc) {
  case 0:
    $where[] = "not isLoc";
    break;
  case 1:
    $where[] = "isLoc";
    break;
}

// Process the $paradigm argument
switch ($paradigm) {
  case 0:
    $where[] = "modelType = 'T'";
    break;
  case 1:
    $where[] = "modelType != 'T'";
    break;
}

// Process the $structStatus argument
if ($structStatus) {
  $joins['entryLexem'] = true;
  $joins['entry'] = true;
  $where[] = "e.structStatus = {$structStatus}";
}

// Process the $structuristId argument
if ($structuristId != Entry::STRUCTURIST_ID_ANY) {
  $joins['entryLexem'] = true;
  $joins['entry'] = true;
  $where[] = "e.structuristId = {$structuristId}";
}

// Process the $nick argument
if ($nick) {
  $user = User::get_by_nick($nick);
  if ($user) {
    $joins['entryLexem'] = true;
    $joins['definition'] = true;
    $where[] = "userId = {$user->id}";
  }
}

// Assemble the query
$query = Model::factory('Lexem')
  ->table_alias('l')
  ->distinct()
  ->order_by_asc('formNoAccent');

// ... and joins
foreach ($joins as $join => $ignored) {
  switch ($join) {
    case 'entryLexem':
      $query = $query->join('EntryLexem', ['l.id', '=', 'el.lexemId'], 'el');
      break;

    case 'definition':
      $query = $query
             ->join('EntryDefinition', ['el.entryId', '=', 'ed.entryId'], 'ed')
             ->join('Definition', 'ed.definitionId = d.id', 'd');
      break;

    case 'entry':
      $query = $query->join('Entry', ['el.entryId', '=', 'e.id'], 'e');
      break;
  }
}

// ... and where clauses
foreach ($where as $clause) {
  $query = $query->where_raw("({$clause})");
}

// Idiorm's count(distinct *) doesn't work
$countResult = $query
  ->select_expr('count(distinct l.id)', 'count')
  ->find_array();
$count = $countResult[0]['count'];
$lexems = $query
        ->select('l.*')
        ->limit(10000)
        ->find_many();

SmartyWrap::assign('count', $count);
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/lexemSearch.tpl');

?>
