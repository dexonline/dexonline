<?php 
require_once("../../phplib/util.php");
Util::assertNotMirror();
Util::assertNotLoggedIn();

$token = Request::get('token');
$identity = Request::get('identity');

$pt = PasswordToken::get_by_token($token);
$data = FileCache::get($identity);

if (!$pt) {
  FlashMessage::add('Ați introdus un cod de recuperare incorect.');
} else if ($pt->createDate < time() - 24 * 3600) {
  FlashMessage::add('Codul de recuperare introdus a expirat.');
} else if (!$data) {
  FlashMessage::add('Ați introdus o identitate incorectă.');
} else {
  $user = User::get_by_id($pt->userId);
  if (!$user) {
    FlashMessage::add('Ați introdus un cod de recuperare incorect.');
  } else if ($user->identity) {
    FlashMessage::add('Acest cont a fost deja revendicat de o identitate OpenID.');
  } else {
    FlashMessage::add('Contul dumneavoastră a fost recuperat și unificat cu identitatea OpenID.', 'success');
    Session::login($user, $data);
  }
}
SmartyWrap::display('auth/passwordRecoveryWrongData.tpl');

?>
