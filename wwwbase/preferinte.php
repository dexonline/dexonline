<?php
require_once("../phplib/Core.php");

$email = Request::get('email');
$name = Request::get('name');
$password = Request::get('password');
$newPassword = Request::get('newPassword');
$newPassword2 = Request::get('newPassword2');
$saveButton = Request::has('saveButton');

$user = User::getActive();

if ($saveButton) {

  $errors = $user
    ? validate($email, $password, $newPassword, $newPassword2)
    : null;

  if ($errors) {
    SmartyWrap::assign('errors', $errors);
  } else {
    // fields applicable only to logged in users
    if ($user) {
      $user->email = $email;
      $user->name = $name;
      if ($newPassword) {
        $user->password = md5($newPassword);
      }
      $user->save();
    }

    // fields applicable to logged in and anonymous users
    $detailsVisible = Request::has('detailsVisible');
    $userPrefs = Request::get('userPrefs');
    $widgets = Request::get('widgets');
    Preferences::set($user, $detailsVisible, array_sum($userPrefs), array_sum($widgets));

    FlashMessage::add('Am salvat preferințele.', 'success');
    Util::redirect('preferinte');
  }
} else {

  $u = User::getActive();
  if ($u) {
    $email = $u->email;
    $name = $u->name;
  }
}

$detailsVisible = Preferences::getDetailsVisible($user);
$userPrefs = Preferences::getUserPrefs($user);
$widgets = Preferences::getWidgets($user);

SmartyWrap::assign([
  'detailsVisible' => $detailsVisible,
  'userPrefs' => $userPrefs,
  'widgets' => $widgets,

  'email' => $email,
  'name' => $name,
  'password' => $password,
  'newPassword' => $newPassword,
  'newPassword2' => $newPassword2,
]);
SmartyWrap::display('preferinte.tpl');

/*************************************************************************/

function validate($email, $password, $newPassword, $newPassword2) {
  $errors = [];

  $msg = User::canChooseEmail($email);
  if ($msg) {
    $errors['email'][] = $msg;
  }

  if ($password || $newPassword || $newPassword2) {
    if (!$password) {
      $errors['password'][] = 'Introdu parola curentă.';
    } else if (md5($password) != User::getActive()->password) {
      $errors['password'][] = 'Parola curentă este incorectă.';
    }

    if (!$newPassword) {
      $errors['newPassword'][] = 'Introdu noua parolă.';
    } else if (!$newPassword2) {
      $errors['newPassword'][] = 'Introdu parola de două ori pentru verificare.';
    } else if ($newPassword != $newPassword2) {
      $errors['newPassword'][] = 'Parolele nu coincid.';
    } else if (strlen($newPassword) < 8) {
      $errors['newPassword'][] = 'Parola trebuie să aibă minimum 8 caractere.';
    }
  }

  return $errors;
}
