<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_WOTD);
util_assertNotMirror();
RecentLink::createOrUpdate('Word of the Day');

SmartyWrap::assign('sectionTitle', 'Word of the Day');
SmartyWrap::assign('allStatuses', util_getAllStatuses());
SmartyWrap::assign('downloadYear', date("Y",strtotime("+1 month")));
SmartyWrap::assign('downloadMonth', date("m",strtotime("+1 month")));
SmartyWrap::addCss('jqgrid', 'jqueryui');
SmartyWrap::addJs('jquery', 'jqgrid', 'jqueryui', 'wotd');
SmartyWrap::displayAdminPage('admin/wotd.ihtml');
?>
