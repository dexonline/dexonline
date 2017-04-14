<?php
require_once("../phplib/util.php");
User::require(User::PRIV_WOTD);

$artists = Model::factory('WotdArtist')->find_many();

SmartyWrap::assign('artists', $artists);
SmartyWrap::addCss('admin');
SmartyWrap::display('alocare-autori.tpl');

?>
