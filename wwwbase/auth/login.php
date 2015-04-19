<?php 

require_once("../../phplib/util.php");
util_assertNotMirror();
util_assertNotLoggedIn();

$openid = util_getRequestParameter('openid');

switch ($openid) {
case 'google': $openid = "https://accounts.google.com/o/oauth2/auth"; break;
case 'yahoo': $openid = "http://yahoo.com/"; break;
}

if ($openid) {
  // Add protocol if missing
  if (!StringUtil::startsWith($openid, 'http://') &&
      !StringUtil::startsWith($openid, 'https://')) {
    $openid = "http://{$openid}";
  }

  $credentials = Config::get('openid.credentials');
  $host = parse_url($openid, PHP_URL_HOST);

  // Decide if we're using OpenID or OpenID connect
  $isOpenidConnect = true;
  $oidc = new OpenIDConnect($openid);
  if (isset($credentials[$host])) {
    // We have an explicit rule for OpenID Connect in the config file
    list($oidcId, $oidcSecret) = explode('|', $credentials[$host]);

  } else if ($oidc->hasWellKnownConfig()) {
    // The site has a .well-known file, so it uses OpenID Connect
    if ($oidc->supportsDynamicRegistration()) {
      list($oidcId, $oidcSecret) = $oidc->dynamicRegistration();
    } else {
      // OpenID connect, but no dynamic registration and no explicit config.
      // Log this and display an error message.
      log_userlog("Need OpenID Connect registration for {$openid}");
      FlashMessage::add('Momentan nu putem accepta OpenID de la acest furnizor. Problema nu ține de noi, dar vom încerca să o reparăm.');
    }

  } else {
    // asume plain OpenID
    $isOpenidConnect = false;
  }

  if (!FlashMessage::getMessage()) {
    if ($isOpenidConnect) {
      try {
        $oidc->authenticate($oidcId, $oidcSecret);
      } catch (OpenIDException $e) {
        FlashMessage::add($e->getMessage());
      }
    } else {
      $authResult = OpenID::beginAuth($openid, null);
      if ($authResult != null) {
        SmartyWrap::displayWithoutSkin('auth/beginAuth.ihtml');
        exit;
      }
    }
  }
}

SmartyWrap::assign('openid', $openid);
SmartyWrap::assign('page_title', 'Autentificare cu OpenID');
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::display('auth/login.ihtml');

?>
