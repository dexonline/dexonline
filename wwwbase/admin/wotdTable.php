<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_WOTD);
util_assertNotMirror();
RecentLink::createOrUpdate('Cuvântul zilei');

SmartyWrap::assign('downloadYear', date("Y",strtotime("+1 month")));
SmartyWrap::assign('downloadMonth', date("m",strtotime("+1 month")));
SmartyWrap::addCss('jqgrid', 'jqueryui', 'select2');
SmartyWrap::addJs('jquery', 'jqgrid', 'jqueryui', 'select2');
SmartyWrap::displayAdminPage('admin/wotdTable.tpl');
?>
