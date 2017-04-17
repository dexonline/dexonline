<?php
require_once("../phplib/util.php");

$saveButton = Request::has('saveButton');

$user = session_getUser();

if ($saveButton) {
  $detailsVisible = Request::get('detailsVisible', 0);
  $userPrefs = implode(Request::get('userPrefs'), ',');
  $widgets = Request::get('widgets');
  Preferences::set($user, $detailsVisible, $userPrefs, array_sum($widgets));
  FlashMessage::add('Am salvat preferințele.', 'success');
  util_redirect('preferinte');
}

$detailsVisible = Preferences::getDetailsVisible($user);
$userPrefs = Preferences::getUserPrefs($user);
$widgets = Preferences::getWidgets($user);

SmartyWrap::assign('detailsVisible', $detailsVisible);
SmartyWrap::assign('userPrefs', $userPrefs);
SmartyWrap::assign('widgets', $widgets);
SmartyWrap::display('preferinte.tpl');

?>
