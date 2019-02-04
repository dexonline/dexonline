<?php

require_once '../../lib/Core.php';
Util::assertNotLoggedIn();

const NICK_REGEXP = '/^([0-9]|\p{L})([-._ 0-9]|\p{L}){2,}$/u';

$nick = Request::get('nick');
$password = Request::get('password');
$password2 = Request::get('password2');
$email = Request::get('email');
$name = Request::get('name');
$remember = Request::has('remember');
$submitButton = Request::has('submitButton');

if ($submitButton) {
  $errors = validate($nick, $password, $password2, $email);

  if ($errors) {
    SmartyWrap::assign('errors', $errors);
  } else {

    $user = Model::factory('User')->create();
    $user->nick = $nick;
    $user->password = md5($password);
    $user->email = $email ? $email : null;
    $user->name = $name;
    $user->save();
    Session::login($user, $remember);
  }
}

SmartyWrap::assign([
  'nick' => $nick,
  'email' => $email,
  'name' => $name,
  'password' => $password,
  'password2' => $password2,
  'remember' => $remember,
]);
SmartyWrap::display('auth/register.tpl');

/*************************************************************************/

function validate($nick, $password, $password2, $email) {
  $errors = [];

  if (mb_strlen($nick) < 3) {
    $errors['nick'][] = 'Numele de utilizator trebuie să aibă minimum 3 caractere.';
  } else if (!preg_match(NICK_REGEXP, $nick)) {
    $errors['nick'][] = 'Numele de utilizator poate conține litere, cifre, spații și caracterele .-_';
  } else if (User::get_by_nick($nick)) {
    $errors['nick'][] = 'Acest nume de utilizator este deja folosit.';
  }

  User::validateNewPassword($password, $password2, $errors, 'password');

  $msg = User::canChooseEmail($email);
  if ($msg) {
    $errors['email'][] = $msg;
  }

  return $errors;
}
