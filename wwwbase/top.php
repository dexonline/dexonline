<?php
require_once("../phplib/util.php");

SmartyWrap::assign('manualData', TopEntry::getTopData(CRIT_CHARS, SORT_DESC, true));
SmartyWrap::assign('bulkData', TopEntry::getTopData(CRIT_CHARS, SORT_DESC, false));
SmartyWrap::assign('page_title', 'Topul voluntarilor');
SmartyWrap::addCss('tablesorter');
SmartyWrap::addJs('pager', 'tablesorter');
SmartyWrap::displayCommonPageWithSkin('top.ihtml');
?>
