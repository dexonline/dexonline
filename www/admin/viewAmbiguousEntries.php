<?php
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$entries = Entry::loadAmbiguous();

Smart::assign('entries', $entries);
Smart::addResources('admin');
Smart::display('admin/viewAmbiguousEntries.tpl');
