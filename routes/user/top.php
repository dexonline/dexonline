<?php

$manualData = TopEntry::getTopData(TopEntry::SORT_CHARS, true, false);
$lastYearData = TopEntry::getTopData(TopEntry::SORT_CHARS, true, true);
$bulkData = TopEntry::getTopData(TopEntry::SORT_CHARS, false, false);

Smart::assign('manualData', $manualData);
Smart::assign('lastYearData', $lastYearData);
Smart::assign('bulkData', $bulkData);
Smart::addResources('tablesorter');
Smart::display('user/top.tpl');
