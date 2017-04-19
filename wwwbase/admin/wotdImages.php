<?php
require_once("../../phplib/Core.php");
User::require(User::PRIV_WOTD);
Util::assertNotMirror();
RecentLink::add('Imaginea zilei');

SmartyWrap::addCss('elfinder', 'jqueryui', 'admin');
SmartyWrap::addJs('elfinder', 'jqueryui');
SmartyWrap::display('admin/wotdImages.tpl');
?>
