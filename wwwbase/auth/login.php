<?php

require_once("../../phplib/Core.php");
Util::assertNotMirror();
Util::assertNotLoggedIn();

$openid = Request::get('openid');
$fakeUserNick = Request::get('fakeUserNick');
$priv = Request::getArray('priv');
$allPriv = Request::get('allPriv');

$devel = Config::get('global.developmentMode');

if ($fakeUserNick) {
  if (!$devel) {
    FlashMessage::add('Conectarea cu utilizatori de test este permisă doar în development.');
    Util::redirect('login');
  }
  $user = User::get_by_nick($fakeUserNick);
  if (!$user) {
    $user = Model::factory('User')->create();
  }
  $user->identity = 'http://fake.example.com';
  $user->nick = $fakeUserNick;
  if (!$user->name) {
    $user->name = $fakeUserNick;
  }
  if ($allPriv) {
    $user->moderator = User::PRIV_ANY;
  } else {
    $user->moderator = array_sum($priv);
  }
  $user->save();
  Session::login($user, array());
}

switch ($openid) {
  case 'google': $openid = "https://accounts.google.com/o/oauth2/auth"; break;
  case 'yahoo': $openid = "http://me.yahoo.com/"; break;
}

if ($openid) {
  // Add protocol if missing
  if (!Str::startsWith($openid, 'http://') &&
      !Str::startsWith($openid, 'https://')) {
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
      Log::error("Need OpenID Connect registration for {$openid}");
      FlashMessage::add('Momentan nu putem accepta OpenID de la acest furnizor. Problema nu ține de noi, dar vom încerca să o reparăm.');
    }

  } else {
    // asume plain OpenID
    $isOpenidConnect = false;
  }

  if (!FlashMessage::hasErrors()) {
    if ($isOpenidConnect) {
      try {
        $oidc->authenticate($oidcId, $oidcSecret);
      } catch (OpenIDException $e) {
        FlashMessage::add($e->getMessage());
      }
    } else {
      // This returns null on errors; does not return at all on success.
      OpenID::beginAuth($openid, null);
    }
  }
}

if ($devel) {
  SmartyWrap::assign('allowFakeUsers', true);
  SmartyWrap::assign('fakeUserNick', 'test' . rand(10000, 99999));
}

SmartyWrap::assign('openid', $openid);
SmartyWrap::display('auth/login.tpl');
