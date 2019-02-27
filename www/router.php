<?php
require_once '../lib/Core.php';

// strip the URL prefix
Str::startsWith($_SERVER['REQUEST_URI'], Config::URL_PREFIX)
  or die('Please check that Config::URL_PREFIX is well-defined.');

$uri = substr($_SERVER['REQUEST_URI'], strlen(Config::URL_PREFIX));

Router::route($uri);

// if we are still here, show the 404 page
http_response_code(404);
Smart::display('404.tpl');
