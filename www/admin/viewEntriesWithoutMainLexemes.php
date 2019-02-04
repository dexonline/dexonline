<?php
require_once '../../lib/Core.php'; 
User::mustHave(User::PRIV_STRUCT);

$entries = Entry::loadWithoutMainLexemes();

SmartyWrap::assign('entries', $entries);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewEntriesWithoutMainLexemes.tpl');
