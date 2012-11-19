<?php
require_once("../phplib/util.php");
require_once("../phplib/userPreferences.php");
util_assertNotLoggedIn();

$sendButton = util_getRequestParameter('send');

if ($sendButton) {
  $userPrefs = util_getRequestCheckboxArray('userPrefs', ',');
  $skin = util_getRequestParameter('skin');
  session_setAnonymousPrefs($userPrefs);
  if (session_isValidSkin($skin)) {
    session_setSkin($skin);
  }
  FlashMessage::add('Preferințele au fost salvate.', 'info');
  util_redirect('preferinte');
} else {
  $userPrefs = session_getAnonymousPrefs();
  $skin = session_getSkin();
}

foreach (preg_split('/,/', $userPrefs) as $pref) {
  if (isset($userPreferencesSet[$pref]) ) {
    $userPreferencesSet[$pref]['checked'] = true;
  }
}

SmartyWrap::assign('userPrefs', $userPreferencesSet);
SmartyWrap::assign('skin', $skin);
SmartyWrap::assign('availableSkins', pref_getServerPreference('skins'));
SmartyWrap::assign('page_title', 'Preferințe');
SmartyWrap::displayCommonPageWithSkin('preferinte.ihtml');
?>
