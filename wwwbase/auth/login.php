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
  OpenID::beginAuth($openid, null);
  smarty_displayWithoutSkin('common/auth/beginAuth.ihtml');
  exit;
}

smarty_assign('openid', $openid);
smarty_assign('page_title', 'Autentificare cu OpenID');
smarty_assign('suggestHiddenSearchForm', true);
smarty_displayCommonPageWithSkin('auth/login.ihtml');

?>
