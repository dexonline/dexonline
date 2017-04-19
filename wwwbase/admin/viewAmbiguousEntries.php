<?php
require_once("../../phplib/util.php"); 
User::require(User::PRIV_EDIT);
Util::assertNotMirror();

$entries = Entry::loadAmbiguous();

SmartyWrap::assign('entries', $entries);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewAmbiguousEntries.tpl');

?>
