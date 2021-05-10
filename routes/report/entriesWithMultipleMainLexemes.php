<?php

User::mustHave(User::PRIV_STRUCT);

$numEntries = Entry::loadWithMultipleMainLexemes();
$entries = Entry::loadWithMultipleMainLexemes($onlyCount = false);

Smart::assign([
  'numEntries' => $numEntries,
  'entries' => $entries,
]);
Smart::addResources('admin', 'tablesorter');
Smart::display('report/entriesWithMultipleMainLexemes.tpl');
