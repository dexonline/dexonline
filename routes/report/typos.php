<?php

User::mustHave(User::PRIV_EDIT);

$defs = Definition::loadTypos('');

$sources = new SourceDropdown('getAllForTypos', []);

Smart::assign([
  'searchResults' => SearchResult::mapDefinitionArray($defs),
  'sources' => (array)$sources,
]
);
Smart::addResources('admin', 'ldring');
Smart::display('report/typos.tpl');
