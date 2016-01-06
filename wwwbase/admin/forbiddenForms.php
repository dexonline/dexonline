<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
RecentLink::createOrUpdate('Forme interzise');

SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::addCss('select2', 'forbiddenForms');
SmartyWrap::addJs('select2', 'forbiddenForms');
SmartyWrap::displayAdminPage('admin/forbiddenForms.tpl');
?>
