<?php
require_once("../phplib/util.php");
util_assertModerator(PRIV_WOTD);

$artists = Model::factory('WotdArtist')->find_many();

SmartyWrap::assign('artists', $artists);
SmartyWrap::addCss('admin');
SmartyWrap::display('alocare-autori.tpl');

?>
