<?php

/**
 * Two-way routing:
 * (1) Resolve URLs like path1/path2/arg1/arg2 to PHP files + GET arguments;
 * (2) Resolve links like path/to/file to localized URLs.
 **/

class Router {

  // Reverse routes definitions, mapping file names to localized URLs. We
  // prefer this format in order to group all information about one file. We
  // compute the forward routes upon initialization. Files have an implicit
  // .php extension.
  const ROUTES = [
    'article/list' => [
      'en_US.utf8' => 'articles',
      'ro_RO.utf8' => 'articole',
    ],
    'article/view' => [
      'en_US.utf8' => 'article',
      'ro_RO.utf8' => 'articol',
    ],
    'article/rss' => [
      'en_US.utf8' => 'rss/articles',
      'ro_RO.utf8' => 'rss/articole',
    ],

    // sources
    'source/list' => [
      'en_US.utf8' => 'sources',
      'ro_RO.utf8' => 'surse',
    ],
    'source/edit' => [
      'en_US.utf8' => 'edit-source',
      'ro_RO.utf8' => 'editare-sursa',
    ],

    // tags
    'tag/list' => [
      'en_US.utf8' => 'tags',
      'ro_RO.utf8' => 'etichete',
    ],
    'tag/edit' => [
      'en_US.utf8' => 'edit-tag',
      'ro_RO.utf8' => 'editare-eticheta',
    ],
    'tag/definition-version' => [
      'en_US.utf8' => 'definition-version-tags',
      'ro_RO.utf8' => 'etichete-istorie',
    ],

    // visuals
    'visual/elfinder' => [ 'en_US.utf8' => 'visual-elfinder' ],
    'visual/list' => [ 'en_US.utf8' => 'visuals' ],
    'visual/tagger' => [ 'en_US.utf8' => 'visual-tagger' ],
  ];

  // file => list of parameters expected in the URL (none by default)
  const PARAMS = [
    'article/view' => [ 'title' ],
  ];

  private static $fwdRoutes = [];
  private static $relAlternate = [];

  static function init() {
    // compute the forward routes, mapping localized URLs to PHP files
    foreach (self::ROUTES as $file => $locales) {
      foreach ($locales as $url) {
        self::$fwdRoutes[$url] = $file;
      }
    }
  }

  // Executes the corresponding PHP file for this request, then exits.
  // Returns null on routing errors.
  static function route($uri) {
    // strip the GET parameters
    $path = parse_url($uri, PHP_URL_PATH);

    $parts = explode('/', $path);
    $route = array_shift($parts);

    // the route may contain slashes, so try increasingly long segments
    while (!isset(self::$fwdRoutes[$route]) && !empty($parts)) {
      $route .= '/' . array_shift($parts);
    }

    if (!isset(self::$fwdRoutes[$route])) {
      Log::debug('no route found for %s', $path);
      return null;
    }

    // get the PHP file
    $rec = self::$fwdRoutes[$route];
    $file = $rec . '.php';
    Log::debug('routing %s to %s', $path, $file);

    // save any alternate versions in case we need to print them in header tags
    self::setRelAlternate($route, $uri);

    // set additional params if the file expects them and the URL has them
    $params = self::PARAMS[$rec] ?? [];
    for ($i = 0; $i < min(count($params), count($parts)); $i++) {
      $_REQUEST[$params[$i]] = urldecode($parts[$i]);
    }

    require_once $file;
    exit;
  }

  // Returns a human-readable URL for this file.
  static function link($file) {
    $routes = self::ROUTES[$file];
    $rel = $routes[LocaleUtil::getCurrent()]     // current locale
      ?? $routes[Config::DEFAULT_ROUTING_LOCALE] // or default locale
      ?? '';                                     // or home page

    return Config::URL_PREFIX . $rel;
  }

  // Collect URLs for localized versions of this page.
  // See https://support.google.com/webmasters/answer/189077
  static function setRelAlternate($route, $uri) {
    $routes = self::ROUTES[self::$fwdRoutes[$route]];

    if (count($routes) > 1) {
      foreach ($routes as $locale => $langRoute) {
        $langCode = explode('_', $locale)[0];
        $langUri = substr_replace($uri, $langRoute, 0, strlen($route));
        $langUrl = Config::URL_HOST . Config::URL_PREFIX . $langUri;
        self::$relAlternate[$langCode] = $langUrl;
      }
    }
  }

  static function getRelAlternate() {
    return self::$relAlternate;
  }

}
