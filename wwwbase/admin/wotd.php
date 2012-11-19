<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_WOTD);
util_assertNotMirror();
RecentLink::createOrUpdate('Word of the Day');

SmartyWrap::assign('sectionTitle', 'Word of the Day');
SmartyWrap::assign('allStatuses', util_getAllStatuses());
//SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::addCss('jqgrid', 'autocomplete');
SmartyWrap::addJs('jquery', 'jqgrid', 'autocomplete');
SmartyWrap::displayAdminPage('admin/wotd.ihtml');
?>
