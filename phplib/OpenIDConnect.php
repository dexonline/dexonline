<?php

/** Loosely based on https://github.com/jumbojett/OpenID-Connect-PHP **/

class OpenIDConnect {
  private $provider;
  private $wellKnownConfig;
  private $configFetched;
  private $claims;

  function __construct($provider) {
    $this->provider = $provider;
    $this->configFetched = false;
  }

  /* Converts Base64URL-encoded data to Base64. */
  function b64url2b64($base64url) {
    $padding = strlen($base64url) % 4;
    if ($padding > 0) {
      $base64url .= str_repeat('=', 4 - $padding);
    }
    return strtr($base64url, '-_', '+/');
  }

  /**
   * Only decodes the second part (dot-separated).
   **/
  function decodeJWT($jwt) {
    $parts = explode(".", $jwt);
    $base64Url = $this->b64url2b64($parts[1]);
    return json_decode(base64_decode($base64Url), true);
  }

  private function getReturnTo() {
    return util_getFullServerUrl() . "auth/revenireOpenidConnect.php";
  }

  private function getWellKnownUrl() {
    $protocol = parse_url($this->provider, PHP_URL_SCHEME);
    $host = parse_url($this->provider, PHP_URL_HOST);
    return "{$protocol}://{$host}/";
  }

  /**
   * Fetches and parses $host/.well-known/openid-configuration.
   * Returns null on all errors.
   **/
  function fetchWellKnownConfig() {
    if (!$this->configFetched) {
      $this->configFetched = true;
      $url = $this->getWellKnownUrl() . '.well-known/openid-configuration';
      list($contents, $responseCode) = util_fetchUrl($url);
      $this->wellKnownConfig = ($contents && ($responseCode == 200))
        ? json_decode($contents, true)
      : null;
    }
  }

  function hasWellKnownConfig() {
    $this->fetchWellKnownConfig();
    return $this->wellKnownConfig;
  }

  function supportsDynamicRegistration() {
    $this->fetchWellKnownConfig();
    return $this->wellKnownConfig && isset($this->wellKnownConfig['registration_endpoint']);
  }

  /**
   * Attempts to register dynamically. Returns a (client ID, secret) pair or null on all errors.
   **/
  function dynamicRegistration() {
    if (!$this->supportsDynamicRegistration()) {
      return null;
    }
    $json = array(
      'client_name' => 'Dexonline',
      'redirect_uris' => array(self::getReturnTo()),
    );
    $url = $this->wellKnownConfig['registration_endpoint'];
    $jsonResult = util_makePostRequest($url, json_encode($json));
    $result = json_decode($jsonResult, true);
    if (!$result || !isset($result['client_secret'])) {
      return null;
    }
    return array($result['client_id'], $result['client_secret']);
  }

  function authenticate($clientId, $secret) {
    $this->fetchWellKnownConfig();
    if (!$clientId || !$secret) {
      throw new OpenIDException('Autentificare eșuată.');
    }
    $url = $this->wellKnownConfig['authorization_endpoint'];
    $nonce = util_randomCapitalLetterString(32);
    $state = util_randomCapitalLetterString(32);
    session_setVariable('openid_connect_nonce', $nonce);
    session_setVariable('openid_connect_state', $state);
    session_setVariable('openid_connect_provider', $this->provider);
    session_setVariable('openid_connect_client', $clientId);
    session_setVariable('openid_connect_secret', $secret);

    $params = array(
      'client_id' => $clientId,
      'openid.realm' => util_getFullServerUrl(), // request old OpenID 2.0 identifier as well
      'nonce' => $nonce,
      'redirect_uri' => $this->getReturnTo(),
      'response_type' => 'code',
      'scope' => 'openid email',
      'state' => $state,
    );

    $url .= '?' . http_build_query($params, null, '&');
    util_redirect($url);
  }

  /**
   * Requests an access token.
   **/
  function requestToken($code) {
    $this->fetchWellKnownConfig();
    $clientId = session_get('openid_connect_client');
    $secret = session_get('openid_connect_secret');
    if (!$clientId || !$secret) {
      throw new OpenIDException('Autentificare eșuată.');
    }
    if (!isset($this->wellKnownConfig['token_endpoint'])) {
      throw new OpenIDException('Nu pot cere un token.');
    }
    $url = $this->wellKnownConfig['token_endpoint'];

    $params = array(
      'client_id' => $clientId,
      'client_secret' => $secret,
      'code' => $code,
      'grant_type' => 'authorization_code',
      'redirect_uri' => $this->getReturnTo(),
    );

    $query = http_build_query($params, null, '&');
    $jsonResult = util_makePostRequest($url, $query);
    $result = json_decode($jsonResult, true);
    if (!$result || isset($result['error'])) {
      throw new OpenIDException('Eroare la cererea unui token');
    }
    if (!isset($result['id_token'])) {
      throw new OpenIDException('Furnizorul dumneavoastră a refuzat autorizarea');
    }

    $this->claims = $this->decodeJWT($result['id_token']);

    // verify the claims
    if (($this->claims['iss'] != $this->wellKnownConfig['issuer']) ||
        ($clientId != $this->claims['aud'] && !in_array($clientId, $this->claims['aud'])) ||
        ($this->claims['nonce'] != session_get('openid_connect_nonce'))) {
      throw new OpenIDException('Nu pot verifica tokenul. Posibilă încercare de interceptare a sesiunii!');
    }

    return $result['access_token'];
  }

  function getUserInfo($token) {
    if (!isset($this->wellKnownConfig['userinfo_endpoint'])) {
      throw new OpenIDException('Nu pot cere datele dumneavoastră de la furnizor.');
    }
    $url = $this->wellKnownConfig['userinfo_endpoint'];
    $url .= "?schema=openid&access_token={$token}";
    list($contents, $responseCode) = util_fetchUrl($url);
    return ($contents && ($responseCode == 200))
      ? json_decode($contents, true)
      : null;
  }

  // Returns the OpenID 2.0 identity for this OpenID Connect identity, if known
  function getPlainOpenid() {
    return ($this->claims && isset($this->claims['openid_id']))
      ? $this->claims['openid_id']
      : null;
  }
}

class OpenIDException extends Exception {
}

?>
