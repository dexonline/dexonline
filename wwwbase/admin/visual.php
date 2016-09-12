<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_VISUAL);
util_assertNotMirror();
RecentLink::add('Dicționarul vizual');

SmartyWrap::addCss('elfinder', 'jqueryui', 'admin');
SmartyWrap::addJs('elfinder', 'jqueryui');
SmartyWrap::display('admin/visual.tpl');
?>
