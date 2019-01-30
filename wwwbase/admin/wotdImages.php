<?php
require_once '../../phplib/Core.php';
User::mustHave(User::PRIV_WOTD);
RecentLink::add('Imaginea zilei');

SmartyWrap::addCss('elfinder', 'jqueryui', 'admin');
SmartyWrap::addJs('elfinder', 'jqueryui');
SmartyWrap::display('admin/wotdImages.tpl');
