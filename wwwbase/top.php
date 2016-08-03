<?php
require_once("../phplib/util.php");

SmartyWrap::assign('manualData', TopEntry::getTopData(CRIT_CHARS, SORT_DESC, true));
SmartyWrap::assign('bulkData', TopEntry::getTopData(CRIT_CHARS, SORT_DESC, false));
SmartyWrap::addCss('tablesorter');
SmartyWrap::addJs('tablesorter');
SmartyWrap::display('top.tpl');
?>
