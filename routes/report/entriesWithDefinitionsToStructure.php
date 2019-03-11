<?php

User::mustHave(User::PRIV_STRUCT);

$entries = Entry::loadWithDefinitionsToStructure();

Smart::assign('entries', $entries);
Smart::addResources('admin');
Smart::display('report/entriesWithDefinitionsToStructure.tpl');
