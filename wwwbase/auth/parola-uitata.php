<?php

require_once("../../phplib/Core.php");
Util::assertNotMirror();
Util::assertNotLoggedIn();

$email = Request::get('email');
$submitButton = Request::has('submitButton');

SmartyWrap::assign('email', $email);

if ($submitButton) {
  $errors = validate($email);

  if ($errors) {
    SmartyWrap::assign('errors', $errors);
  } else {

    $user = User::get_by_email($email);
    if ($user) {
      Log::notice("Password recovery requested for $email from " . $_SERVER['REMOTE_ADDR']);

      // Create the token
      $pt = Model::factory('PasswordToken')->create();
      $pt->userId = $user->id;
      $pt->token = Str::randomCapitalLetters(20);
      $pt->save();

      // Send email
      SmartyWrap::assign([
        'homePage' => Request::getFullServerUrl(),
        'token' => $pt->token,
      ]);
      $body = SmartyWrap::fetch('email/resetPassword.tpl');
      $ourEmail = Config::get('global.contact');
      $headers = [
        "From: dexonline <$ourEmail>",
        "Reply-To: $ourEmail",
        'Content-Type: text/plain; charset=UTF-8',
      ];
      $result = mail($email,
                     'Schimbarea parolei pentru dexonline',
                     $body,
                     implode("\r\n", $headers));
    }

    // Display a confirmation even for incorrect addresses.
    SmartyWrap::display('auth/passwordRecoveryEmailSent.tpl');
    exit;
  }
}

SmartyWrap::display('auth/parola-uitata.tpl');

/*************************************************************************/

function validate($email) {
  $errors = [];

  if (!$email) {
    $errors['email'] = 'Adresa de e-mail nu poate fi vidă.';
  } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Adresa de e-mail pare incorectă.';
  }

  return $errors;
}
