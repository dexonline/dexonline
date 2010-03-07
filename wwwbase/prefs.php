<?php
require_once("../phplib/util.php");
require_once("../phplib/session.php");
require_once("../phplib/userPreferences.php");

$sendButton = util_getRequestParameter('send');

if ($sendButton) {
  $userPrefs = util_getRequestCheckboxArray('userPrefs', ',');
  session_setAnonymousPrefs($userPrefs);
}
else {
  $userPrefs = session_getAnonymousPrefs();
}

foreach (split(',', $userPrefs) as $pref) {
  if (isset($userPreferencesSet[$pref]) ) {
    $userPreferencesSet[$pref]['checked'] = true;
  }
}

smarty_assign('send', !empty($sendButton));
smarty_assign('userPrefs', $userPreferencesSet);
smarty_assign('page_title', 'DEX online - Preferințe');
smarty_assign('show_search_box', 0);

smarty_displayCommonPageWithSkin('prefs.ihtml');
?>
