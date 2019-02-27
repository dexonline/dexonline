<?php
User::mustHave(User::PRIV_VISUAL);
RecentLink::add('Dicționarul vizual');

Smart::addResources('elfinder', 'admin');
Smart::display('visual/list.tpl');
