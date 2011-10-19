<?php 
require_once("../phplib/util.php");
util_assertNotMirror();
util_assertNotLoggedIn();

$token = util_getRequestParameter('token');
$password = util_getRequestParameter('password');
$password2 = util_getRequestParameter('password2');
$submitButton = util_getRequestParameter('submitButton');

$pt = PasswordToken::get("token = '{$token}'");
$user = null;
if (!$pt) {
  flash_add('Ați introdus un cod de recuperare incorect.');
} else if ($pt->createDate < time() - 24 * 3600) {
  flash_add('Codul de recuperare introdus a expirat.');
} else {
  $user = User::get("id = {$pt->userId}");
  if (!$user) {
    flash_add('Ați introdus un cod de recuperare incorect.');
  }
}

if ($user && $submitButton) {
  if (strlen($password) < 4 || strlen($password) > 16) {
    flash_add('Parola trebuie să aibă între 4 și 16 caractere.');
  } else if ($password != $password2) {
    flash_add('Parolele nu coincid.');
  } else {
    $user->password = md5($password);
    $user->save();
    $pt->delete();
    flash_add('Noua parolă a fost salvată.', 'info');
    session_login($user);
  }
}

smarty_assign('user', $user);
smarty_assign('token', $token);
smarty_displayCommonPageWithSkin('recuperare-parola.ihtml');

?>
