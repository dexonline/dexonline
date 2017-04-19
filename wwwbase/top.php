<?php
require_once("../phplib/util.php");

$manualData = TopEntry::getTopData(TopEntry::SORT_CHARS, SORT_DESC, true);
$bulkData = TopEntry::getTopData(TopEntry::SORT_CHARS, SORT_DESC, false);

SmartyWrap::assign('manualData', $manualData);
SmartyWrap::assign('bulkData', $bulkData);
SmartyWrap::addCss('tablesorter');
SmartyWrap::addJs('tablesorter');
SmartyWrap::display('top.tpl');
?>
