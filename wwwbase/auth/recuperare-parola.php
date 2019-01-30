<?php
require_once '../../phplib/Core.php';
Util::assertNotMirror();
Util::assertNotLoggedIn();

$token = Request::get('token');
$password = Request::get('password');
$password2 = Request::get('password2');
$submitButton = Request::has('submitButton');

$pt = PasswordToken::get_by_token($token);

// Validate the token and load the user
$user = null;
if (!$pt) {
  FlashMessage::add('Ați introdus un cod de recuperare incorect.');
} else if ($pt->createDate < time() - 24 * 3600) {
  FlashMessage::add('Codul de recuperare introdus a expirat.');
} else {
  $user = User::get_by_id($pt->userId);
  if (!$user) {
    FlashMessage::add('Ați introdus un cod de recuperare incorect.');
  }
}

if ($user && $submitButton) {

  $errors = validate($password, $password2);

  if ($errors) {
    SmartyWrap::assign('errors', $errors);
  } else {
    $user->password = md5($password);
    $user->save();
    $pt->delete();
    FlashMessage::add('Ți-ai recuperat cu succes contul.', 'success');
    Session::login($user);
  }

}

SmartyWrap::assign([
  'token' => $token,
  'user' => $user,
  'password' => $password,
  'password2' => $password2,
]);
SmartyWrap::display('auth/passwordRecovery.tpl');

/*************************************************************************/

function validate($password, $password2) {
  $errors = [];

  User::validateNewPassword($password, $password2, $errors, 'password');

  return $errors;
}
