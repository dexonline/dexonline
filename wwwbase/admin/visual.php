<?php
require_once("../../phplib/util.php");
User::require(User::PRIV_VISUAL);
util_assertNotMirror();
RecentLink::add('Dicționarul vizual');

SmartyWrap::addCss('elfinder', 'jqueryui', 'admin');
SmartyWrap::addJs('elfinder', 'jqueryui');
SmartyWrap::display('admin/visual.tpl');
?>
