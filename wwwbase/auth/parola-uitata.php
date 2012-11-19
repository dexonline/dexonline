<?php

require_once("../../phplib/util.php");
util_assertNotMirror();
util_assertNotLoggedIn();

$submitButton = util_getRequestParameter('submitButton');
$identity = util_getRequestParameter('identity');
$email = util_getRequestParameter('email');

SmartyWrap::assign('identity', $identity);
SmartyWrap::assign('email', $email);
SmartyWrap::assign('page_title', 'Parolă uitată');
SmartyWrap::assign('suggestHiddenSearchForm', true);

if (!$submitButton) {
  SmartyWrap::displayCommonPageWithSkin('auth/parola-uitata.ihtml');
} else if (!$email) {
  FlashMessage::add('Trebuie să introduceți o adresă de e-mail.');
} else {
  $user = User::get_by_email($email);
  if ($user) {
    log_userLog("Password recovery requested for $email from " . $_SERVER['REMOTE_ADDR']);
    
    // Create the token
    $pt = Model::factory('PasswordToken')->create();
    $pt->userId = $user->id;
    $pt->token = util_randomCapitalLetterString(20);
    $pt->save();
    
    // Send email
    SmartyWrap::assign('homePage', util_getFullServerUrl());
    SmartyWrap::assign('token', $pt->token);
    $body = SmartyWrap::fetch('email/resetPassword.ihtml');
    $ourEmail = pref_getContactEmail();
    $result = mail($email, "Schimbarea parolei pentru DEX online", $body, "From: DEX online <$ourEmail>\r\nReply-To: $ourEmail");
    
    // Display a confirmation even for incorrect addresses.
    SmartyWrap::displayCommonPageWithSkin('auth/passwordRecoveryEmailSent.ihtml');
  }
}

?>
