<?php
require_once '../../lib/Core.php'; 
User::mustHave(User::PRIV_STRUCT);

$entries = Entry::loadWithoutMainLexemes();

Smart::assign('entries', $entries);
Smart::addCss('admin');
Smart::display('admin/viewEntriesWithoutMainLexemes.tpl');
