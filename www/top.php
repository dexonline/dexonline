<?php
require_once '../lib/Core.php';

$manualData = TopEntry::getTopData(TopEntry::SORT_CHARS, SORT_DESC, true);
$bulkData = TopEntry::getTopData(TopEntry::SORT_CHARS, SORT_DESC, false);

Smart::assign('manualData', $manualData);
Smart::assign('bulkData', $bulkData);
Smart::addCss('tablesorter');
Smart::addJs('tablesorter');
Smart::display('top.tpl');
