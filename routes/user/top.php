<?php

$manualData = TopEntry::getTopData(TopEntry::SORT_CHARS, SORT_DESC, true);
$lastyearData = TopEntry::getTopData(TopEntry::SORT_CHARS, SORT_DESC, true, true);
$bulkData = TopEntry::getTopData(TopEntry::SORT_CHARS, SORT_DESC, false);

Smart::assign('manualData', $manualData);
Smart::assign('lastyearData', $lastyearData);
Smart::assign('bulkData', $bulkData);
Smart::addResources('tablesorter');
Smart::display('user/top.tpl');
