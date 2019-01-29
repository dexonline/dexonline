<?php
require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_EDIT | User::PRIV_STRUCT);
Util::assertNotMirror();

define('SECONDS_PER_DAY', 86400);

// entry parameters
$description = Request::get('description');
$structStatus = Request::get('structStatus');
$structuristId = Request::get('structuristId');
$entryTagIds = Request::getArray('entryTagIds');

// lexeme parameters
$formNoAccent = Request::get('formNoAccent');
$paradigm = Request::get('paradigm');
$lexemeTagIds = Request::getArray('lexemeTagIds');
$modelTypes = Request::getArray('modelTypes');
$restrictions = Request::getArray('restrictions');

// definition parameters
$lexicon = Request::get('lexicon');
$status = Request::get('status');
$sourceId = Request::get('sourceId');
$structured = Request::get('structured');
$userId = Request::get('userId');
$startDate = Request::get('startDate');
$endDate = Request::get('endDate');

// other parameters
$view = Request::get('view');
$page = Request::get('page', 1);
$prevPageButton = Request::has('prevPageButton');
$nextPageButton = Request::has('nextPageButton');
$submitButton = Request::has('submitButton');

$q = Model::factory($view);
$joinEntry = $joinLexeme = $joinDefinition = false;
$joinEntryTag = $joinLexemeTag = false;

// process entry parameters
if ($description) {
  $joinEntry = true;
  extendQueryWithRegexField($q, 'e.description', $description);
}

if ($structStatus) {
  $joinEntry = true;
  $q = $q->where('e.structStatus', $structStatus);
}

if ($structuristId != Entry::STRUCTURIST_ID_ANY) {
  $joinEntry = true;
  $q = $q->where('e.structuristId', $structuristId);
}

if (!empty($entryTagIds)) {
  $joinEntry = $joinEntryTag = true;
  $q = $q
     ->where('eot.objectType', ObjectTag::TYPE_ENTRY)
     ->where_in('eot.tagId', $entryTagIds);
}

if (!empty($lexemeTagIds)) {
  $joinLexeme = $joinLexemeTag = true;
  $q = $q
     ->where('lot.objectType', ObjectTag::TYPE_LEXEME)
     ->where_in('lot.tagId', $lexemeTagIds);
}

// process lexeme parameters
if ($formNoAccent) {
  $joinLexeme = true;
  extendQueryWithRegexField($q, 'l.formNoAccent', $formNoAccent);
}

if ($paradigm !== '') {
  $joinLexeme = true;
  if ($paradigm) {
    $q = $q->where_not_equal('l.modelType', 'T');
  } else {
    $q = $q->where('l.modelType', 'T');
  }
}

if (!empty($modelTypes)) {
  $joinLexeme = true;
  $q = $q->where_in('l.modelType', $modelTypes);
}

if ($restrictions) {
  $joinLexeme = true;
  foreach (str_split($restrictions) as $letter) {
    $q = $q->where_like('l.restriction', "%{$letter}%");
  }
}

// process definition parameters
if ($lexicon) {
  $joinDefinition = true;
  extendQueryWithRegexField($q, 'd.lexicon', $lexicon);
}

if ($status != '') {
  $joinDefinition = true;
  $q = $q->where('d.status', $status);
}

if ($sourceId) {
  $joinDefinition = true;
  $q = $q->where('d.sourceId', $sourceId);
}

if ($structured !== '') {
  $joinDefinition = true;
  $q = $q->where('d.structured', $structured);
}

if ($userId) {
  $joinDefinition = true;
  $q = $q->where('d.userId', $userId);
}

if ($startDate) {
  $joinDefinition = true;
  // the "!" sets the hh:mm:ss to 0
  $startTs = DateTime::createFromFormat('!Y-m-d', $startDate)->getTimestamp();
  $q = $q->where_gte('d.createDate', $startTs);
}

if ($endDate) {
  $joinDefinition = true;
  $endTs = DateTime::createFromFormat('!Y-m-d', $endDate)->getTimestamp();
  $q = $q->where_lt('d.createDate', $endTs + SECONDS_PER_DAY);
}

// assemble the joins -- can't seem to do it any better than the naive way
switch ($view) {
  case 'Entry':
    if ($joinLexeme) {
      $q = $q
         ->join('EntryLexeme', ['e.id', '=', 'el.entryId'], 'el')
         ->join('Lexeme', ['el.lexemeId', '=', 'l.id'], 'l');
    }
    if ($joinDefinition) {
      $q = $q
         ->join('EntryDefinition', ['e.id', '=', 'ed.entryId'], 'ed')
         ->join('Definition', ['ed.definitionId', '=', 'd.id'], 'd');
    }
    break;

  case 'Lexeme':
    if ($joinEntry || $joinDefinition) {
      $q = $q
         ->join('EntryLexeme', ['l.id', '=', 'el.lexemeId'], 'el')
         ->join('Entry', ['el.entryId', '=', 'e.id'], 'e');
    }
    if ($joinDefinition) {
      $q = $q
         ->join('EntryDefinition', ['e.id', '=', 'ed.entryId'], 'ed')
         ->join('Definition', ['ed.definitionId', '=', 'd.id'], 'd');
    }
    break;

  case 'Definition':
    if ($joinEntry || $joinLexeme) {
      $q = $q
         ->join('EntryDefinition', ['d.id', '=', 'ed.definitionId'], 'ed')
         ->join('Entry', ['ed.entryId', '=', 'e.id'], 'e');
    }
    if ($joinLexeme) {
      $q = $q
         ->join('EntryLexeme', ['e.id', '=', 'el.entryId'], 'el')
         ->join('Lexeme', ['el.lexemeId', '=', 'l.id'], 'l');
    }
    break;
}

if ($joinEntryTag) {
  $q = $q->join('ObjectTag', ['e.id', '=', 'eot.objectId'], 'eot');
}

if ($joinLexemeTag) {
  $q = $q->join('ObjectTag', ['l.id', '=', 'lot.objectId'], 'lot');
}

const VIEW_DATA = [
  'Definition' => [
    'alias' => 'd',
    'order' => 'd.lexicon',
    'pageSize' => 500,
  ],
  'Entry' => [
    'alias' => 'e',
    'order' => 'e.description',
    'pageSize' => 10000,
  ],
  'Lexeme' => [
    'alias' => 'l',
    'order' => 'l.formNoAccent',
    'pageSize' => 10000,
  ],
];

// order the results
$alias = VIEW_DATA[$view]['alias'];
$order = VIEW_DATA[$view]['order'];
$q = $q->table_alias($alias);

if ($joinEntryTag || $joinLexemeTag) {
  $expectedCount = (count($entryTagIds) ?: 1) * (count($lexemeTagIds) ?: 1);
  $q = $q
     ->group_by("{$alias}.id")
     ->having_raw("count(*) = {$expectedCount}");
}

// Count the results. Note: we cannot use the count() method, because of the grouping above.
$countResult = $q
  ->select("{$alias}.id")
  ->distinct()
  ->find_array();
$count = count($countResult);

// fetch a page of data
if ($prevPageButton && $page > 1) {
  $page--;
}
if ($nextPageButton) {
  $page++;
}
$pageSize = VIEW_DATA[$view]['pageSize'];
$numPages = floor(($count - 1) / $pageSize) + 1;
$offset = ($page - 1) * $pageSize;

$data = $q
      ->select("{$alias}.*")
      ->distinct()
      ->order_by_asc($order)
      ->offset($offset)
      ->limit($pageSize)
      ->find_many();

if ($view == 'Definition') {
  $data = SearchResult::mapDefinitionArray($data);
}

// make a copy of the form data (with some changes) for page scrolling
$args = $_REQUEST;
unset($args['submitButton'], $args['prevPageButton'], $args['nextPageButton']);
$args['page'] = $page;

$stats = [
  'page' => $page,
  'numPages' => $numPages,
  'firstResult' => $offset + 1,
  'lastResult' => min($offset + $pageSize, $count),
  'numResults' => $count,
];

SmartyWrap::assign('view', $view);
SmartyWrap::assign('data', $data);
SmartyWrap::assign('args', $args);
SmartyWrap::assign('stats', $stats);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/advancedSearch.tpl');

/*************************************************************************/

function extendQueryWithRegexField(&$query, $fieldName, $value) {
  if (Str::hasRegexp($value)) {
    $r = Str::dexRegexpToMysqlRegexp($value);
    $query = $query->where_raw("{$fieldName} {$r}");
  } else {
    $query = $query->where($fieldName, $value);
  }
}
