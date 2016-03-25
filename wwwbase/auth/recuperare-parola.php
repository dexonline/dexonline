<?php 
require_once("../../phplib/util.php");
util_assertNotMirror();
util_assertNotLoggedIn();

$token = util_getRequestParameter('token');
$identity = util_getRequestParameter('identity');

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
    session_login($user, $data);
  }
}
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::display('auth/passwordRecoveryWrongData.tpl');

?>
