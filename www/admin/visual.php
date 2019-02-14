<?php
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_VISUAL);
RecentLink::add('Dicționarul vizual');

Smart::addResources('elfinder', 'admin');
Smart::display('admin/visual.tpl');
