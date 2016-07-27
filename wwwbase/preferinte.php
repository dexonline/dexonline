<?php
require_once("../phplib/util.php");

$sendButton = util_getRequestParameter('send');

$user = session_getUser();

if ($sendButton) {
  $detailsVisible = util_getRequestParameterWithDefault('detailsVisible', 0);
  $userPrefs = util_getRequestCheckboxArray('userPrefs', ',');
  $widgets = util_getRequestParameter('widgets');
  Preferences::set($user, $detailsVisible, $userPrefs, array_sum($widgets));
  FlashMessage::add('Am salvat preferințele.', 'success');
  util_redirect('preferinte');
}

$detailsVisible = Preferences::getDetailsVisible($user);
$userPrefs = Preferences::getUserPrefs($user);
$widgets = Preferences::getWidgets($user);

SmartyWrap::assign('detailsVisible', $detailsVisible);
SmartyWrap::assign('userPrefs', $userPrefs);
SmartyWrap::assign('privilegeNames', $PRIV_NAMES);
SmartyWrap::assign('widgets', $widgets);
SmartyWrap::display('preferinte.tpl');

?>
