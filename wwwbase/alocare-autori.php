<?php
require_once("../phplib/util.php");
util_assertModerator(PRIV_WOTD);

$artists = Model::factory('WotdArtist')->find_many();

SmartyWrap::assign('artists', $artists);
SmartyWrap::assign('suggestNoBanner', true);
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::addCss('wotdAssignment');
SmartyWrap::addJs('wotdAssignment');
SmartyWrap::display('alocare-autori.tpl');

?>
