<?php

require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$mentions = Mention::getDetailedTreeMentions();

SmartyWrap::assign('mentions', $mentions);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewTreeMentions.tpl');
