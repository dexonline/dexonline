<?php 
require_once("../phplib/util.php");
util_assertNotMirror();
util_assertNotLoggedIn();

$target = util_getRequestParameter('target');
$nickOrEmail = util_getRequestParameter('email');
$password = util_getRequestParameter('password');
$loginButton = util_getRequestParameter('login');
$forgetButton = util_getRequestParameter('forget');

if (!$target) {
  $target = util_getWwwRoot();
}

smarty_assign('nickOrEmail', $nickOrEmail);
smarty_assign('password', $password);
smarty_assign('login', !empty($loginButton));
smarty_assign('forget', !empty($forgetButton));

if ($loginButton) {
  // User tried to log in
  if ($nickOrEmail == '') {
    flash_add('Trebuie să introduceți adresa de email sau numele de cont.');
  } else if ($password == '') {
    flash_add('Trebuie să introduceți parola.');
  } else {
    $user = User::get(sprintf("password = '%s' and (email = '%s' or nick = '%s')", md5($password), $nickOrEmail, $nickOrEmail));
    if ($user) {
      session_login($user);
      util_redirect($target);
    } else {
      flash_add('Adresa de email, numele de cont sau parola sunt greșite.');
      log_userLog("Unsuccessful login attempt email=$nickOrEmail ip=" . $_SERVER['REMOTE_ADDR']);
    }
  }
} else if ($forgetButton) {
  if ($nickOrEmail == '') {
    flash_add('Pentru a vă reseta parola, trebuie să introduceți adresa de email.');
  } else {
    $user = User::get("email = '$nickOrEmail'");
    if ($user) {
      log_userLog("Password recovery requested for $nickOrEmail from " . $_SERVER['REMOTE_ADDR']);
      // Create the token
      $pt = new PasswordToken();
      $pt->userId = $user->id;
      $pt->token = util_randomCapitalLetterString(20);
      $pt->save();

      // Send email
      smarty_assign('homePage', util_getFullServerUrl());
      smarty_assign('token', $pt->token);
      $body = smarty_fetch('email/resetPassword.ihtml');
      $email = pref_getContactEmail();
      $result = mail($nickOrEmail, "Schimbarea parolei pentru DEX online", $body, "From: DEX online <$email>\r\nReply-To: $email");
    }
    // Display a confirmation even for incorrect addresses.
    smarty_assign('page_title', 'Recuperarea parolei');
    smarty_displayCommonPageWithSkin('passwordRecoveryEmailSent.ihtml');
    exit;
  }
}

smarty_assign('page_title', 'Conectare utilizator');
smarty_assign('suggestHiddenSearchForm', true);
smarty_displayCommonPageWithSkin('login.ihtml');
?>
