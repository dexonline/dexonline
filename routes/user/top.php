<?php

$manualData = TopEntry::getTopData(TopEntry::SORT_CHARS, SORT_DESC, true);
$bulkData = TopEntry::getTopData(TopEntry::SORT_CHARS, SORT_DESC, false);

Smart::assign('manualData', $manualData);
Smart::assign('bulkData', $bulkData);
Smart::addResources('tablesorter');
Smart::display('user/top.tpl');
