<?php
require_once("../phplib/util.php");
util_assertNotMirror();

// Set of all customizable user preferences
$userPreferencesSet = array(
  'CEDILLA_BELOW' => array(
	  'value' => 1 << 0,
    'label' => 'Vreau să văd ş și ţ cu sedilă (în loc de virguliță)',
    'comment' => 'Scrierea corectă este cu &#x219; și &#x21b; în loc de ş și ţ, dar este posibil ca aceste simboluri să nu fie afișate corect în browserul dumneavoastră.',
    'checked' => false, 
  ),
  'FORCE_DIACRITICS' => array(
    'value' => 1 << 1,
    'label' => 'Pun eu diacritice în căutare',
    'comment' => 'Fără această opțiune, o căutare după „mal” va returna și rezultatele pentru „mâl”. Cu această opțiune, rezultatele pentru „mâl” nu mai sunt returnate decât ' .
                 'când căutați explicit „mâl”.',
    'checked' => false, 
  ),
  'OLD_ORTHOGRAPHY' => array(
    'value' => 1 << 2,
    'label' => 'Folosesc ortografia folosită pînă în 1993 (î din i)',
    'comment' => 'Până în 1993, „&#xe2;” era folosit doar în cuvântul „român”, în cuvintele derivate și în unele nume proprii.',
    'checked' => false, 
  ),
  'EXCLUDE_UNOFFICIAL' => array(
  	'value' => 1 << 3,
	'label' => 'Vreau să vizualizez numai definițiile „oficiale”',
	'comment' => 'Sursele „neoficiale” nu au girul niciunei instituții acreditate de Academia Română sau a vreunei edituri de prestigiu', 
	'checked' => false,
  ),
);

// Define error codes
define("OK", 0);
define("ERR_NO_NICK", 1);
define("ERR_NICK_LEN", 2);
define("ERR_NICK_CHARS", 3);
define("ERR_PASS_LEN", 4);
define("ERR_PASS_MISMATCH", 5);
define("ERR_NO_EMAIL", 6);
define("ERR_BAD_EMAIL", 7);
define("ERR_NICK_TAKEN", 8);
define("ERR_EMAIL_TAKEN", 9);
define("ERR_CUR_PASS", 10);
define("ERR_OTHER", 11);

$sendButton = util_getRequestParameter('send');
$nick = util_getRequestParameter('nick');
$newPass = util_getRequestParameter('newPass');
$newPass2 = util_getRequestParameter('newPass2');
$curPass = util_getRequestParameter('curPass');
$name = util_getRequestParameter('name');
$email = util_getRequestParameter('email');
$emailVisible = util_getRequestParameter('emailVisible');
$userPrefs = util_getRequestCheckboxArray('userPrefs', ',');

$user = session_getUser();
if (!$user) {
  util_redirect('login.php');
}

$error = OK;
if ($sendButton) {

  // First, a few syntactic checks
  if ($nick == "") {
    $error = ERR_NO_NICK;
  } else if (strlen($nick) < 3) {
    $error = ERR_NICK_LEN;
  } else if ($nick != preg_replace("/[^a-zA-Z0-9_-]/", "", $nick)) {
    $error = ERR_NICK_CHARS;
  } else if (strlen($newPass) > 0 && strlen($newPass) < 4) {
    $error = ERR_PASS_LEN;
  } else if ($newPass != $newPass2) {
    $error = ERR_PASS_MISMATCH;
  } else if ($email == "") {
    $error = ERR_NO_EMAIL;
  } else if (!strstr($email, '.') || !strstr($email, '@')) {
    $error = ERR_BAD_EMAIL;
  } else if (md5($curPass) != $user->password) {
    $error = ERR_CUR_PASS;
  }

  // Verify that the email address and nickname are unique..
  if (!$error) {
    $userByNick = User::loadByNick($nick);
    if ($userByNick && $userByNick->id != $user->id) {
      $error = ERR_NICK_TAKEN;
    }
  }
  
  if (!$error) {
    $userByEmail = User::loadByEmail($email);
    if ($userByEmail && $userByEmail->id != $user->id) {
      $error = ERR_EMAIL_TAKEN;
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
    $user->prefs = $userPrefs;
    $user->save();
    session_setUser($user);
  }
} else {
  $nick = $user->nick;
  $newPass = '';
  $newPass2 = '';
  $curPass = '';
  $name = $user->name;
  $email = $user->email;
  $emailVisible = $user->emailVisible;
  $userPrefs = $user->prefs;
}

foreach(split(',',$userPrefs) as $pref) {
  if( isset($userPreferencesSet[$pref]) ) {
    $userPreferencesSet[$pref]['checked'] = true;
  }
}

smarty_assign('error_code', $error);
smarty_assign('send', !empty($sendButton));
smarty_assign('nick', $nick);
smarty_assign('newPass', $newPass);
smarty_assign('newPass2', $newPass2);
smarty_assign('curPass', $curPass);
smarty_assign('name', $name);
smarty_assign('email', $email);
smarty_assign('emailVisible', $emailVisible);
smarty_assign('userPrefs', $userPreferencesSet);
smarty_assign('page_title', 'DEX online - Modificare date personale');
smarty_assign('show_search_box', 0);

smarty_displayCommonPageWithSkin('account.ihtml');
?>
