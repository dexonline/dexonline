<?php
require_once("../phplib/util.php");
require_once("../phplib/userPreferences.php");
util_assertNotMirror();

$sendButton = util_getRequestParameter('send');
$nick = util_getRequestParameter('nick');
$newPass = util_getRequestParameter('newPass');
$newPass2 = util_getRequestParameter('newPass2');
$curPass = util_getRequestParameter('curPass');
$name = util_getRequestParameter('name');
$email = util_getRequestParameter('email');
$emailVisible = util_getRequestParameter('emailVisible');
$userPrefs = util_getRequestCheckboxArray('userPrefs', ',');
$skin = util_getRequestParameter('skin');

$user = session_getUser();
if (!$user) {
  util_redirect('login.php');
}

if ($sendButton) {
  // First, a few syntactic checks
  $error = true;
  if ($nick == "") {
    session_setFlash('Trebuie să vă alegeți un nume de cont.');
  } else if (strlen($nick) < 3) {
    session_setFlash('Numele de cont trebuie să aibă minim 3 caractere.');
  } else if ($nick != preg_replace("/[^a-zA-Z0-9_-]/", "", $nick)) {
    session_setFlash('Numele de cont poate conține numai caracterele indicate.');
  } else if (strlen($newPass) > 0 && strlen($newPass) < 4) {
    session_setFlash('Trebuie să vă alegeți o parolă de minim 4 caractere.');
  } else if ($newPass != $newPass2) {
    session_setFlash('Parolele nu coincid.');
  } else if ($email == "") {
    session_setFlash('Trebuie să precizați noua adresă de email.');
  } else if (!strstr($email, '.') || !strstr($email, '@')) {
    session_setFlash('Adresa de email nu este validă.');
  } else if (md5($curPass) != $user->password) {
    session_setFlash('Parola actuală este incorectă.');
  } else {
    $error = false;
  }

  // Verify that the email address and nickname are unique..
  if (!$error) {
    $userByNick = User::get("nick = '$nick'");
    if ($userByNick && $userByNick->id != $user->id) {
      $error = true;
      session_setFlash('Acest nume de cont este deja folosit.');
    }
  }
  
  if (!$error) {
    $userByEmail = User::get("email = '$email'");
    if ($userByEmail && $userByEmail->id != $user->id) {
      $error = true;
      session_setFlash('Această adresă de email este deja folosită.');
    }
  }
  
  // Things are swell, edit account and display acknowledgement
  if (!$error) {
    $user->nick = $nick;
    $user->name = $name;
    $user->email = $email;
    $user->emailVisible = $emailVisible ? 1 : 0;
    if ($newPass) {
      $user->password = md5($newPass);
    }
    $user->preferences = $userPrefs;
    if (session_isValidSkin($skin)) {
      $user->skin = $skin;
    }
    $user->save();
    session_setUser($user);
    session_setFlash('Informațiile au fost salvate.', 'info');
  }
} else {
  $nick = $user->nick;
  $newPass = '';
  $newPass2 = '';
  $curPass = '';
  $name = $user->name;
  $email = $user->email;
  $emailVisible = $user->emailVisible;
  $skin = session_getSkin();
  if (is_string($user->prefs)) {
    // Legacy code for people who were logged in when we migrated User to AdoDB.
    // After a new login this problem will go away.
    // TODO: Remove this code after April 3rd 2010.
    $userPrefs = $user->prefs;
  } else {
    $userPrefs = $user->preferences;
  }
}

foreach (split(',', $userPrefs) as $pref) {
  if (isset($userPreferencesSet[$pref]) ) {
    $userPreferencesSet[$pref]['checked'] = true;
  }
}

smarty_assign('nick', $nick);
smarty_assign('newPass', $newPass);
smarty_assign('newPass2', $newPass2);
smarty_assign('curPass', $curPass);
smarty_assign('name', $name);
smarty_assign('email', $email);
smarty_assign('emailVisible', $emailVisible);
smarty_assign('userPrefs', $userPreferencesSet);
smarty_assign('skin', $skin);
smarty_assign('availableSkins', pref_getServerPreference('skins'));
smarty_assign('page_title', 'DEX online - Contul meu');
smarty_displayCommonPageWithSkin('account.ihtml');
?>
