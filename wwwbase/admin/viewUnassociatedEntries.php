<?php

require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
RecentLink::createOrUpdate('Intrări neasociate');

$entries = Entry::loadUnassociated();

SmartyWrap::assign('entries', $entries);
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/viewUnassociatedEntries.tpl');

?>
