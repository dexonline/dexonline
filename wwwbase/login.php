<?php 
require_once("../phplib/util.php");
util_assertNotMirror();

/*
 * TODO: - Check for empty $email
 *       - Warn if user forgot password, and submitted user name, not e-mail address
 */

define("OK", 0);             // Things went ok, for login or for password reset
define("ERR_NO_NICK", 1);    // No nick/email supplied for login
define("ERR_NO_EMAIL", 2);   // No email supplied for password reset
define("ERR_NO_PASS", 3);    // No password supplied for login
define("ERR_BAD_LOGIN", 4);  // Incorrect nick/email/password combination
define("ERR_BAD_EMAIL", 5);  // Incorrect email supplied for password reset
define("ERR_NO_MESSAGE", 6); // Password was reset, but couldn't send email

$target = util_getRequestParameter('target');
$nickOrEmail = util_getRequestParameter('email');
$password = util_getRequestParameter('password');
$loginButton = util_getRequestParameter('login');
$forgetButton = util_getRequestParameter('forget');

if (!$target) {
  $target = 'index.php';
}

smarty_assign('target', $target);
smarty_assign('nickOrEmail', $nickOrEmail);
smarty_assign('password', $password);
smarty_assign('login', !empty($loginButton));
smarty_assign('forget', !empty($forgetButton));

$error = OK;
if ($loginButton) {
  // User tried to log in
  if ($nickOrEmail == '') {
    $error = ERR_NO_NICK;
  } else if ($password == '') {
    $error = ERR_NO_PASS;
  } else {
    $user = User::loadByNickEmailPassword($nickOrEmail, md5($password));
    if ($user) {
      session_login($user);
    } else {
      // Login failed
      $error = ERR_BAD_LOGIN;
      log_userLog("Unsuccessful login attempt email=$nickOrEmail ip=" . $_SERVER['REMOTE_ADDR']);
    }
  }
} else if ($forgetButton) {
  if ($nickOrEmail == '') {
    $error = ERR_NO_EMAIL;
  } else {
    $user = User::loadByEmail($nickOrEmail);
    if ($user) {
      $password = util_randomCapitalLetterString(12);
      $user->password = md5($password);
      $user->save();

      // All set! Email the new password to that email address
      $body = "Buna ziua,

Primiti acest mesaj deoarece ati solicitat schimbarea parolei pentru contul
DEX online asociat cu adresa de email $nickOrEmail. Noua parola este:

$password

Dupa ce va conectati cu aceasta parola, o puteti schimba apasand pe
\"Contul meu\". Daca nu dumneavoastra ati solicitat aceasta schimbare,
va rugam raspundeti la acest mesaj pentru ca este posibil ca cineva sa
incerce sa va foloseasca contul in mod abuziv.

Toate cele bune,
Echipa DEX online
";

      log_userLog("Password reset for $nickOrEmail requested from " . $_SERVER['REMOTE_ADDR']);
      $email = pref_getContactEmail();
      $result = mail($nickOrEmail, "Noua parola pentru DEX online", $body,
		     "From: DEX online <$email>\r\n" .
		     "Reply-To: $email");
      
      if (!$result) {
	$error = ERR_NO_MESSAGE;
      }
    } else {
      $error = ERR_BAD_EMAIL;
    }
  }
}

smarty_assign('error_code', $error);
smarty_assign('page_title', 'DEX online - Conectare utilizator');
smarty_assign('show_search_box', 0);
smarty_displayCommonPageWithSkin('login.ihtml');
?>
