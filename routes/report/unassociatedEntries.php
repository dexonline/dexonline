<?php

User::mustHave(User::PRIV_EDIT);

$entries = Entry::loadUnassociated();

Smart::assign('entries', $entries);
Smart::addResources('admin');
Smart::display('report/unassociatedEntries.tpl');
