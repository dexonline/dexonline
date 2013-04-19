<?php
require_once("../phplib/util.php");

SmartyWrap::assign('page_title', 'Liste de forme pentru Scrabble');
SmartyWrap::assign('locVersions', pref_getLocVersions());
SmartyWrap::displayCommonPageWithSkin('scrabble-forme.ihtml');

?>
