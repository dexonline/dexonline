<?php

require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$entries = Entry::loadUnassociated();

SmartyWrap::assign('entries', $entries);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewUnassociatedEntries.tpl');
