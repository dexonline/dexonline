<?php
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_STRUCT);

$entries = Entry::loadWithoutMainLexemes();

Smart::assign('entries', $entries);
Smart::addResources('admin');
Smart::display('report/entriesWithoutMainLexemes.tpl');
