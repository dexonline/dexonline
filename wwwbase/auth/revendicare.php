<?php 
require_once("../../phplib/Core.php");
Util::assertNotMirror();
Util::assertNotLoggedIn();

$loginType = Request::get('loginType');
$nickOrEmail = Request::get('nickOrEmail');
$password = Request::get('password');
$nick = Request::get('nick');
$randString = Request::get('randString');

$data = FileCache::get($randString);
if (!$data) {
  FlashMessage::add('A apărut o eroare la autentificare. Vă rugăm încercați din nou.');
} else {
  if ($loginType == 0) {
    $user = Model::factory('User')->where('password', md5($password))->where_raw("(email = '{$nickOrEmail}' or nick = '{$nickOrEmail}')")->find_one();
    if (!$user) {
      FlashMessage::add('Numele de utilizator sau parola sunt incorecte.');
    } else if ($user->identity) {
      FlashMessage::add('Acest utilizator a fost deja revendicat de un alt OpenID.');
    } else {
      Session::login($user, $data);
    }
  } else {
    $openidNick = ($loginType == 1) ? $data['fullname'] : (($loginType == 2) ? $data['nickname'] : $nick);
    $user = User::get_by_nick($openidNick);
    if ($user) {
      FlashMessage::add('Acest nume de utilizator este deja luat.');
    } else if (mb_strlen($openidNick) < 3 || mb_strlen($openidNick) > 20) {
      FlashMessage::add('Numele de utilizator trebuie să aibă între 3 și 20 de caractere.');
    } else if (!preg_match("/^([-a-z0-9_. ]|ă|â|î|ș|ț|Ă|Â|Î|Ș|Ț)+$/i", $openidNick)) {
      FlashMessage::add('Numele de utilizator poate conține doar litere, cifre, spații și simbolurile . - _');
    } else if (!preg_match("/[a-z]|ă|â|î|ș|ț|Ă|Â|Î|Ș|Ț/i", $openidNick)) {
      FlashMessage::add('Numele de utilizator trebuie să conțină cel puțin o literă.');
    } else {
      $data['nickname'] = $openidNick;
      Session::login(null, $data);
    }
  }
}

SmartyWrap::assign('data', $data);
SmartyWrap::assign('chosenNick', $nick);
SmartyWrap::assign('password', $password);
SmartyWrap::assign('randString', $randString);
SmartyWrap::assign('loginType', $loginType);
SmartyWrap::display('auth/chooseIdentity.tpl');  

?>
