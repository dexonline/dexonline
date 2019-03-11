<?php
User::mustHave(User::PRIV_WOTD);

$artists = Model::factory('WotdArtist')->find_many();

Smart::assign('artists', $artists);
Smart::addResources('admin');
Smart::display('artist/assign.tpl');
