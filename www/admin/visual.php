<?php
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_VISUAL);
RecentLink::add('Dicționarul vizual');

Smart::addCss('elfinder', 'jqueryui', 'admin');
Smart::addJs('elfinder', 'jqueryui');
Smart::display('admin/visual.tpl');
