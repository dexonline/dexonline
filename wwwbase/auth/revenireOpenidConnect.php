<?php 
require_once("../../phplib/util.php");
util_assertNotMirror();
util_assertNotLoggedIn();

$error = util_getRequestParameter('error');
$errorDescription = util_getRequestParameter('error_description');
$code = util_getRequestParameter('code');
$state = util_getRequestParameter('state');
$provider = session_get('openid_connect_provider');

try {
  $oidc = new OpenIDConnect($provider);
  if ($error) {
    throw new OpenIDException($errorDescription);
  }
  if (!$code || !$state || ($state != session_get('openid_connect_state'))) {
    throw new OpenIDException('Răspuns incorect de la server');
  }
  if (!$provider) {
    throw new OpenIDException('Sesiune coruptă');
  }
  $token = $oidc->requestToken($code);
  $data = $oidc->getUserInfo($token);
  if (!isset($data['sub'])) {
    throw new OpenIDException('Date incorecte de la furnizor');
  }
} catch (OpenIDException $e) {
  FlashMessage::add('Eroare la autentificare: ' . $e->getMessage());
  util_redirect('login.php');
}

// With OpenID connect, the user is uniquely identified by (provider, sub).
// We store the provider in the User.identity field for backwards compatibility with OpenID.
// We also rename the name field to fullname, plain OpenID style
$data['identity'] = $provider;
if (isset($data['name'])) {
  $data['fullname'] = $data['name'];
}
$user = User::get_by_identity_openidConnectSub($provider, $data['sub']);

if (!$user && $oidc->getPlainOpenid()) {
  // This may be the first time the user logs in after the migration from
  // OpenID 2.0 to OpenID Connect.
  $user = User::get_by_identity($oidc->getPlainOpenid());
  if ($user) {
    $user->identity = null; // session_login will overwrite it
  }
}

if ($user) {
  session_login($user, $data);
} else {
  // First time logging in, must claim an existing account or create a new one
  // TODO this duplicates code in revenireOpenid.php
  $user = isset($data['email']) ? User::get_by_email($data['email']) : null;
  $loginType = $user ? 0 : (isset($data['fullname']) ? 1 : (isset($data['nickname']) ? 2 : 3));

  // Store the identity in a temporary file. Don't print it in the form, because then it can be faked on the next page.
  $randString = util_randomCapitalLetterString(20);
  FileCache::put($randString, $data);

  SmartyWrap::assign('page_title', 'Autentificare cu OpenID');
  SmartyWrap::assign('suggestHiddenSearchForm', true);
  SmartyWrap::assign('data', $data);
  SmartyWrap::assign('randString', $randString);
  SmartyWrap::assign('loginType', $loginType);
  SmartyWrap::display('auth/chooseIdentity.ihtml');  
}

?>
