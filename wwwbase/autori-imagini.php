<?php
require_once("../phplib/util.php");
util_assertModerator(PRIV_WOTD);

$artists = Model::factory('WotdArtist')->find_many();

SmartyWrap::assign('artists', $artists);
SmartyWrap::display('autori-imagini.tpl');

?>
