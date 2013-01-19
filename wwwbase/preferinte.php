<?php
require_once("../phplib/util.php");

$sendButton = util_getRequestParameter('send');
$detailsVisible = util_getRequestParameterWithDefault('detailsVisible', 0);
$userPrefs = util_getRequestCheckboxArray('userPrefs', ',');
$skin = util_getRequestParameter('skin');

$user = session_getUser();

if ($sendButton) {
  Preferences::set($user, $detailsVisible, $userPrefs, $skin);
  FlashMessage::add('Preferințele au fost salvate.', 'info');
  util_redirect('preferinte');
}

list($detailsVisible, $userPrefs, $skin) = Preferences::get($user);

SmartyWrap::assign('detailsVisible', $detailsVisible);
SmartyWrap::assign('userPrefs', $userPrefs);
SmartyWrap::assign('skin', $skin);
SmartyWrap::assign('availableSkins', pref_getServerPreference('skins'));
SmartyWrap::assign('privilegeNames', $PRIV_NAMES);
SmartyWrap::assign('page_title', 'Preferințe');
SmartyWrap::displayCommonPageWithSkin('preferinte.ihtml');

?>
