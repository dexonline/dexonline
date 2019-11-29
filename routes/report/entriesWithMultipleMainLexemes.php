<?php

User::mustHave(User::PRIV_STRUCT);

$entries = Entry::loadWithMultipleMainLexemes($onlyCount = false, $limit = 2000);
$prep = Str::getAmountPreposition(count($entries));

Smart::assign([
  'prep' => $prep,
  'entries' => $entries,
]);
Smart::addResources('admin', 'tablesorter');
Smart::display('report/entriesWithMultipleMainLexemes.tpl');
