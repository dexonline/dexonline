<?php
User::mustHave(User::PRIV_WOTD);
RecentLink::add('Imaginea zilei');

Smart::addResources('elfinder', 'admin');
Smart::display('wotd/images.tpl');
