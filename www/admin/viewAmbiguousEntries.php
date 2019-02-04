<?php
require_once '../../lib/Core.php'; 
User::mustHave(User::PRIV_EDIT);

$entries = Entry::loadAmbiguous();

SmartyWrap::assign('entries', $entries);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewAmbiguousEntries.tpl');
