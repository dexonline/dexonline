<?php

require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$mentions = Mention::getDetailedTreeMentions();

Smart::assign('mentions', $mentions);
Smart::addCss('admin');
Smart::display('admin/viewTreeMentions.tpl');
