<?php

/**
 * Strike mode. Allow privileged users to continue working.
 * If necessary, should be generalized to allow more parametrization.
 *
 * Sample config:
 *
 * const PLUGINS = [
 *   'Strike' => [
 *     // make sure to include a timezone
 *     'startDate' => '20/Sep/2019:00:00:00 GMT+0300',
 *     'endDate' => '20/Sep/2019:23:59:59 GMT+0300',
 *   ],
 * ];
 *
 **/

class Strike extends Plugin {

  private $active;

  const ALLOWED_ROUTES = [
    'auth/login',
    'auth/logout',
  ];

  // checks if the current URI is a localized version of an allowed route
  private function isAllowedRoute() {
    $uri = substr($_SERVER['REQUEST_URI'], strlen(Config::URL_PREFIX));

    foreach (self::ALLOWED_ROUTES as $r) {
      if (in_array($uri, Router::ROUTES[$r])) {
        return true;
      }
    }

    return false;
  }

  function __construct($cfg) {
    $from = strtotime($cfg['startDate']);
    $to = strtotime($cfg['endDate']);
    $now = time();


    $this->active =
      ($now >= $from) &&
      ($now < $to) &&
      !$this->isAllowedRoute() &&
      !User::can(User::PRIV_ANY);
  }

  function cssJsSmarty() {
    if ($this->active) {
      Smart::addPluginCss('strike/strike.css');
    }
  }

  function coreInit() {
    if ($this->active) {
      Smart::display('plugins/strike/strike.tpl', true);
      exit;
    }
  }

}
