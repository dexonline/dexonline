<?php
require_once '../lib/Core.php';
User::mustHave(User::PRIV_WOTD);

$artists = Model::factory('WotdArtist')->find_many();

Smart::assign('artists', $artists);
Smart::addCss('admin');
Smart::display('alocare-autori.tpl');
