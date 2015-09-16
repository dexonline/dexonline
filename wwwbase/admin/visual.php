<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_VISUAL);
util_assertNotMirror();
RecentLink::createOrUpdate('Adăugare imagini definiții');

SmartyWrap::addCss('elfinder', 'jqueryui');
SmartyWrap::addJs('jquery', 'jqueryui', 'elfinder', 'visual');
SmartyWrap::displayAdminPage('admin/visual.tpl');
?>
