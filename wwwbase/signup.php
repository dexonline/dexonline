<?php
require_once("../phplib/util.php");
util_assertNotMirror();

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
define("ERR_OTHER", 10);

$sendButton = util_getRequestParameter('send');
$nick = util_getRequestParameter('nick');
$password = util_getRequestParameter('password');
$password2 = util_getRequestParameter('password2');
$name = util_getRequestParameter('name');
$email = util_getRequestParameter('email');
$emailVisible = util_getRequestParameter('emailVisible');

smarty_assign('send', !empty($sendButton));
smarty_assign('nick', $nick);
smarty_assign('password', $password);
smarty_assign('password2', $password2);
smarty_assign('name', $name);
smarty_assign('email', $email);
smarty_assign('emailVisible', $emailVisible);

$error = OK;
if ($sendButton) {
  // First, a few syntactic checks
  if ($nick == "") {
    $error = ERR_NO_NICK;
  } else if (strlen($nick) < 3) {
    $error = ERR_NICK_LEN;
  } else if ($nick != preg_replace("/[^a-zA-Z0-9_-]/", "", $nick)) {
    $error = ERR_NICK_CHARS;
  } else if (strlen($password) < 4) {
    $error = ERR_PASS_LEN;
  } else if ($password != $password2) {
    $error = ERR_PASS_MISMATCH;
  } else if ($email == "") {
    $error = ERR_NO_EMAIL;
  } else if (!strstr($email, '.') || !strstr($email, '@')) {
    $error = ERR_BAD_EMAIL;
  }

  // Connect to the database and verify that there are no duplicates.
  // The email address and nickname must be unique.
  if ($error == OK && User::get("nick = '$nick'")) {
    $error = ERR_NICK_TAKEN;
  }

  if ($error == OK && User::get("email = '$email'")) {
    $error = ERR_EMAIL_TAKEN;
  }

  // Things are swell, create account and display acknowledgement
  if ($error == OK) {
    $user = new User();
    $user->nick = $nick;
    $user->name = $name;
    $user->email = $email;
    $user->emailVisible = $emailVisible ? 1 : 0;
    $user->password = md5($password);
    $user->save();
  }
}

smarty_assign('error_code', $error);
smarty_assign('page_title', 'Creare cont');
smarty_displayCommonPageWithSkin('signup.ihtml');
?>
