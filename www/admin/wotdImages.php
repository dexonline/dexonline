<?php
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_WOTD);
RecentLink::add('Imaginea zilei');

Smart::addCss('elfinder', 'jqueryui', 'admin');
Smart::addJs('elfinder', 'jqueryui');
Smart::display('admin/wotdImages.tpl');
