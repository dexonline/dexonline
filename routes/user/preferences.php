<?php

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
    Smart::assign('errors', $errors);
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
    $userPrefs = Request::getArray('userPrefs');
    $tabs = Request::get('tabs');
    $widgets = Request::getArray('widgets');
    Preferences::set(
      $user,
      $detailsVisible,
      array_sum($userPrefs),
      $tabs,
      array_sum($widgets),
    );

    FlashMessage::add(_('Your preferences have been saved.'), 'success');
    Util::redirectToSelf();
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

Smart::assign([
  'detailsVisible' => $detailsVisible,
  'userPrefs' => $userPrefs,
  'widgets' => $widgets,

  'email' => $email,
  'name' => $name,
  'password' => $password,
  'newPassword' => $newPassword,
  'newPassword2' => $newPassword2,
]);
Smart::addResources('sortable');
Smart::display('user/preferences.tpl');

/*************************************************************************/

function validate($email, $password, $newPassword, $newPassword2) {
  $errors = [];

  $msg = User::canChooseEmail($email);
  if ($msg) {
    $errors['email'][] = $msg;
  }

  if ($password || $newPassword || $newPassword2) {
    if (!$password) {
      $errors['password'][] = _('Enter your current password.');
    } else if (md5($password) != User::getActive()->password) {
      $errors['password'][] = _('The current password is incorrect.');
    }

    User::validateNewPassword($newPassword, $newPassword2, $errors, 'newPassword');
  }

  return $errors;
}
