<?php
require_once("../../phplib/Core.php"); 
User::mustHave(User::PRIV_EDIT | User::PRIV_STRUCT);
Util::assertNotMirror();

$description = Request::get('description');
$structStatus = Request::get('structStatus');
$structuristId = Request::get('structuristId');

$query = Model::factory('Entry');

$where = [];
$joins = [];

// Process the $form argument
if ($description) {
  if (StringUtil::hasRegexp($description)) {
    $r = StringUtil::dexRegexpToMysqlRegexp($description);
    $query = $query->where_raw("description {$r}");
  } else {
    $query = $query->where('description', $description);
  }
}

// Process the $structStatus argument
if ($structStatus) {
  $query = $query->where('structStatus', $structStatus);
}

// Process the $structuristId argument
if ($structuristId != Entry::STRUCTURIST_ID_ANY) {
  $query = $query->where('structuristId', $structuristId);
}

$query = $query->order_by_asc('description');

$count = $query->count();
$entries = $query->limit(10000)->find_many();

SmartyWrap::assign('count', $count);
SmartyWrap::assign('entries', $entries);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/entrySearch.tpl');

?>
