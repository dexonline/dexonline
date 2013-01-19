<?php
require_once("../phplib/util.php");

$sendButton = util_getRequestParameter('send');

$user = session_getUser();

if ($sendButton) {
  $detailsVisible = util_getRequestParameterWithDefault('detailsVisible', 0);
  $userPrefs = util_getRequestCheckboxArray('userPrefs', ',');
  $skin = util_getRequestParameter('skin');
  $widgets = util_getRequestParameter('widgets');
  Preferences::set($user, $detailsVisible, $userPrefs, $skin, array_sum($widgets));
  FlashMessage::add('Preferințele au fost salvate.', 'info');
  util_redirect('preferinte');
}

$detailsVisible = Preferences::getDetailsVisible($user);
$userPrefs = Preferences::getUserPrefs($user);
$skin = Preferences::getSkin($user);
$widgets = Preferences::getWidgets($user);

SmartyWrap::assign('detailsVisible', $detailsVisible);
SmartyWrap::assign('userPrefs', $userPrefs);
SmartyWrap::assign('skin', $skin);
SmartyWrap::assign('availableSkins', pref_getServerPreference('skins'));
SmartyWrap::assign('privilegeNames', $PRIV_NAMES);
SmartyWrap::assign('widgets', $widgets);
SmartyWrap::assign('page_title', 'Preferințe');
SmartyWrap::displayCommonPageWithSkin('preferinte.ihtml');

?>
