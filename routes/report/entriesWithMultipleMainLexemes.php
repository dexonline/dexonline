<?php

User::mustHave(User::PRIV_STRUCT);

$entries = Entry::loadWithMultipleMainLexemes($onlyCount = false, $limit = 100);

Smart::assign('entries', $entries);
Smart::addResources('admin', 'tablesorter');
Smart::display('report/entriesWithMultipleMainLexemes.tpl');
