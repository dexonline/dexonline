<?php
require_once("../phplib/Core.php");

$saveButton = Request::has('saveButton');

$user = User::getActive();

if ($saveButton) {
  $detailsVisible = Request::get('detailsVisible', 0);
  $userPrefs = Request::get('userPrefs');
  $widgets = Request::get('widgets');
  Preferences::set($user, $detailsVisible, array_sum($userPrefs), array_sum($widgets));
  FlashMessage::add('Am salvat preferințele.', 'success');
  Util::redirect('preferinte');
}

$detailsVisible = Preferences::getDetailsVisible($user);
$userPrefs = Preferences::getUserPrefs($user);
$widgets = Preferences::getWidgets($user);

SmartyWrap::assign('detailsVisible', $detailsVisible);
SmartyWrap::assign('userPrefs', $userPrefs);
SmartyWrap::assign('widgets', $widgets);
SmartyWrap::display('preferinte.tpl');

?>
