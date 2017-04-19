<?php

require_once("../../phplib/Core.php");
User::require(User::PRIV_EDIT);
Util::assertNotMirror();

$entries = Entry::loadUnassociated();

SmartyWrap::assign('entries', $entries);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewUnassociatedEntries.tpl');

?>
