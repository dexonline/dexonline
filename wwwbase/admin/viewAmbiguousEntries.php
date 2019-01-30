<?php
require_once '../../phplib/Core.php'; 
User::mustHave(User::PRIV_EDIT);

$entries = Entry::loadAmbiguous();

SmartyWrap::assign('entries', $entries);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewAmbiguousEntries.tpl');
