<?php
require_once("../phplib/util.php");

$user = session_getUser();
$haveEuPlatescCredentials = pref_getSectionPreference('euplatesc', 'euPlatescMid') && pref_getSectionPreference('euplatesc', 'euPlatescKey');

SmartyWrap::assign('page_title', 'Sprijină dexonline!');
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::assign('suggestNoBanner', true);
SmartyWrap::assign('haveEuPlatescCredentials', $haveEuPlatescCredentials);
SmartyWrap::assign('defaultEmail', $user ? $user->email : '');
SmartyWrap::displayCommonPageWithSkin('doneaza.ihtml');

/**************************************************************************/

?>
