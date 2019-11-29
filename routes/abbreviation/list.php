<?php
User::mustHave(User::PRIV_ADMIN | User::PRIV_EDIT);

$sources = new SourceDropdown('getAllForAbbreviations', []);

Smart::assign('sources', (array)$sources);
Smart::addResources('ldring', 'tablesorter');
Smart::display('abbreviation/list.tpl');
