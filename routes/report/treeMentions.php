<?php

User::mustHave(User::PRIV_EDIT);

$mentions = Mention::getDetailedTreeMentions();

Smart::assign('mentions', $mentions);
Smart::addResources('admin');
Smart::display('report/treeMentions.tpl');
