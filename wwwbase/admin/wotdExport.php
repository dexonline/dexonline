<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_WOTD);

$month = util_getRequestParameter('month');
$year = util_getRequestParameter('year');

$month = sprintf("%02d", $month);
$wotds = Model::factory('WordOfTheDay')->where_like('displayDate', "{$year}-{$month}-%")->order_by_asc('displayDate')->find_many();
$wotdSet = array();

foreach($wotds as $wotd) {
  $wotdr = WordOfTheDayRel::get_by_wotdId($wotd->id);
  $def = Definition::get_by_id($wotdr->refId);
  $wotdSet[] = array('wotd' => $wotd, 'def' => $def);
}

SmartyWrap::assign('sectionTitle', "Descărcarea cuvintelor zilei - {$month}/{$year}");
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::assign('wotdSet', $wotdSet);
SmartyWrap::displayAdminPage('admin/wotdExport.tpl');
?>
