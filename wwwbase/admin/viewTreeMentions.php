<?php

require_once("../../phplib/util.php");
User::require(User::PRIV_EDIT);
util_assertNotMirror();

$mentions = Mention::getDetailedTreeMentions();

SmartyWrap::assign('mentions', $mentions);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewTreeMentions.tpl');

?>
