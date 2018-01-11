<?php 
require_once("../../phplib/Core.php");
Util::assertNotMirror();
Util::assertNotLoggedIn();

$data = OpenID::finishAuth();
if (!$data) {
  SmartyWrap::display('auth/login.tpl');
  exit();
}

$user = User::get_by_identity($data['identity']);
if ($user) {
  Session::login($user, $data);
} else {
  // First time logging in, must claim an existing account or create a new one
  $user = isset($data['email']) ? User::get_by_email($data['email']) : null;
  $loginType = $user ? 0 : (isset($data['fullname']) ? 1 : (isset($data['nickname']) ? 2 : 3));

  // Store the identity in a temporary file. Don't print it in the form, because then it can be faked on the next page.
  $randString = Str::randomCapitalLetters(20);
  FileCache::put($randString, $data);

  SmartyWrap::assign('data', $data);
  SmartyWrap::assign('randString', $randString);
  SmartyWrap::assign('loginType', $loginType);
  SmartyWrap::display('auth/chooseIdentity.tpl');  
}

?>
