<?php
require_once("../../phplib/util.php");
User::require(User::PRIV_WOTD);
util_assertNotMirror();
RecentLink::add('Imaginea zilei');

SmartyWrap::addCss('elfinder', 'jqueryui', 'admin');
SmartyWrap::addJs('elfinder', 'jqueryui');
SmartyWrap::display('admin/wotdImages.tpl');
?>
