<?php
require_once '../../lib/Core.php'; 
User::mustHave(User::PRIV_STRUCT);

$entries = Entry::loadWithDefinitionsToStructure();

SmartyWrap::assign('entries', $entries);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewEntriesWithDefinitionsToStructure.tpl');
