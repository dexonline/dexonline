<?php
require_once("../phplib/util.php");

$user = session_getUser();
$haveEuPlatescCredentials = Config::get('euplatesc.euPlatescMid') && Config::get('euplatesc.euPlatescKey');

SmartyWrap::assign('page_title', 'Sprijină dexonline!');
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::assign('suggestNoBanner', true);
SmartyWrap::assign('haveEuPlatescCredentials', $haveEuPlatescCredentials);
SmartyWrap::assign('defaultEmail', $user ? $user->email : '');
SmartyWrap::displayCommonPageWithSkin('doneaza.ihtml');

/**************************************************************************/

?>
