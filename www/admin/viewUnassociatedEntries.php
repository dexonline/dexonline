<?php

require_once '../../phplib/Core.php';
User::mustHave(User::PRIV_EDIT);

$entries = Entry::loadUnassociated();

SmartyWrap::assign('entries', $entries);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewUnassociatedEntries.tpl');
