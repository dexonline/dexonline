<?php

require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$mentions = Mention::getDetailedTreeMentions();

SmartyWrap::assign('mentions', $mentions);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewTreeMentions.tpl');
