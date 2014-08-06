<?php 

require_once("../../phplib/util.php");
util_assertNotMirror();
util_assertNotLoggedIn();

$openid = util_getRequestParameter('openid');

switch ($openid) {
case 'google': $openid = "https://www.google.com/accounts/o8/id"; break;
case 'yahoo': $openid = "http://yahoo.com/"; break;
}

if ($openid) {
  $authResult = OpenID::beginAuth($openid, null);
  if ($authResult != null)
  {
    SmartyWrap::displayWithoutSkin('auth/beginAuth.ihtml');
    exit;
  }
}

SmartyWrap::assign('openid', $openid);
SmartyWrap::assign('page_title', 'Autentificare cu OpenID');
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::displayCommonPageWithSkin('auth/login.ihtml');

?>
