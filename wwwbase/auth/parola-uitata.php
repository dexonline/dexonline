<?php

require_once("../../phplib/util.php");
Util::assertNotMirror();
Util::assertNotLoggedIn();

$submitButton = Request::get('submitButton');
$identity = Request::get('identity');
$email = Request::get('email');

SmartyWrap::assign('identity', $identity);
SmartyWrap::assign('email', $email);

if ($submitButton) {
  if (!$email) {
    FlashMessage::add('Trebuie să introduceți o adresă de e-mail.');
    SmartyWrap::display('auth/parola-uitata.tpl');
  } else {
    $user = User::get_by_email($email);
    if ($user) {
      Log::notice("Password recovery requested for $email from " . $_SERVER['REMOTE_ADDR']);

      // Create the token
      $pt = Model::factory('PasswordToken')->create();
      $pt->userId = $user->id;
      $pt->token = StringUtil::randomCapitalLetters(20);
      $pt->save();

      // Send email
      SmartyWrap::assign('homePage', Request::getFullServerUrl());
      SmartyWrap::assign('token', $pt->token);
      $body = SmartyWrap::fetch('email/resetPassword.tpl');
      $ourEmail = Config::get('global.contact');
      $headers = array("From: dexonline <$ourEmail>", "Reply-To: $ourEmail", 'Content-Type: text/plain; charset=UTF-8');
      $result = mail($email, "Schimbarea parolei pentru dexonline", $body, implode("\r\n", $headers));
    }
    // Display a confirmation even for incorrect addresses.
    SmartyWrap::display('auth/passwordRecoveryEmailSent.tpl');
  }
} else {
  SmartyWrap::display('auth/parola-uitata.tpl');
}


?>
