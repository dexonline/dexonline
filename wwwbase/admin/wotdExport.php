<?php
require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_WOTD);

$month = Request::get('month');
$year = Request::get('year');

$month = sprintf("%02d", $month);
$wotds = Model::factory('WordOfTheDay')
  ->where_like('displayDate', "{$year}-{$month}-%")
  ->order_by_asc('displayDate')
  ->find_many();
$wotdSet = [];

foreach($wotds as $wotd) {
  $def = Definition::get_by_id($wotd->definitionId);
  $wotdSet[] = ['wotd' => $wotd, 'def' => $def];
}

SmartyWrap::assign('month', $month);
SmartyWrap::assign('year', $year);
SmartyWrap::assign('wotdSet', $wotdSet);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/wotdExport.tpl');
