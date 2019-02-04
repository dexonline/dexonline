<?php

require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$entries = Entry::loadUnassociated();

Smart::assign('entries', $entries);
Smart::addCss('admin');
Smart::display('admin/viewUnassociatedEntries.tpl');
