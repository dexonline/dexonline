<?php
require_once '../../lib/Core.php'; 
User::mustHave(User::PRIV_STRUCT);

$entries = Entry::loadWithDefinitionsToStructure();

Smart::assign('entries', $entries);
Smart::addCss('admin');
Smart::display('admin/viewEntriesWithDefinitionsToStructure.tpl');
