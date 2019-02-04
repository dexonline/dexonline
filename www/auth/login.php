<?php

require_once '../../lib/Core.php';
Util::assertNotLoggedIn();

// nick means "nick or email" throughout this form
$nick = Request::get('nick');
$password = Request::get('password');
$remember = Request::has('remember');
$submitButton = Request::has('submitButton');

$fakeUserNick = Request::get('fakeUserNick');
$priv = Request::getArray('priv');
$allPriv = Request::get('allPriv');

$devel = Config::DEVELOPMENT_MODE;

if ($fakeUserNick) {
  if (!$devel) {
    FlashMessage::add('Conectarea cu utilizatori de test este permisă doar în development.');
    Util::redirect('login');
  }
  $user = User::get_by_nick($fakeUserNick);
  if (!$user) {
    $user = Model::factory('User')->create();
  }
  $user->nick = $fakeUserNick;
  if (!$user->name) {
    $user->name = $fakeUserNick;
  }
  if ($allPriv) {
    // PRIV_TRAINEE is more of a restriction than a privilege
    $user->moderator = User::PRIV_ANY ^ User::PRIV_TRAINEE;
  } else {
    $user->moderator = array_sum($priv);
  }
  $user->save();
  Session::login($user, true);
}

if ($submitButton) {
  $user = validate($nick, $password, $errors);

  if ($user) {
    Session::login($user, $remember);
  } else {
    SmartyWrap::assign('errors', $errors);
  }
}

if ($devel) {
  SmartyWrap::assign([
    'allowFakeUsers' => true,
    'fakeUserNick' => 'test' . rand(10000, 99999),
  ]);
}

SmartyWrap::assign([
  'nick' => $nick,
  'remember' => $remember,
]);
SmartyWrap::display('auth/login.tpl');

/*************************************************************************/

// returns a user upon successful credentials, null otherwise
function validate($nick, $password, &$errors) {
  $errors = [];

  if (!$nick) {
    $errors['nick'][] = 'Numele de utilizator / adresa de e-mail nu pot fi vide.';
  }

  if (!$password) {
    $errors['password'][] = 'Parola nu poate fi vidă.';
  }

  $user = null;
  if ($nick && $password) {
    $user = Model::factory('User')
      ->where_any_is([['nick' => $nick], ['email' => $nick]])
      ->where('password', md5($password))
      ->find_one();
    if (!$user) {
      $errors['password'][] = 'Numele de utilizator / adresa de e-mail sau parola sunt incorecte.';
    }
  }

  return $user;
}
