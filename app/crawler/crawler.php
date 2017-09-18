<?php

require_once __DIR__ . '/../../phplib/Core.php';
require_once __DIR__ . '/../../phplib/third-party/simple_html_dom.php';
require_once __DIR__ . '/../../phplib/third-party/phpuri.php';

$config = parse_ini_file('crawler.conf', true);

foreach ($config as $section => $vars) {
  if (StringUtil::startsWith($section, 'site-')) {
    $siteId = explode('-', $section, 2)[1];
    $rootUrl = $vars['url'];
    $rootDomain = parse_url($rootUrl, PHP_URL_HOST);
    $linkSelectors = $vars['articleLinkSelector'];
    Log::info("crawling site: {$siteId}, root URL: {$rootUrl}");

    // for converting relative URLs to absolute
    $base = phpUri::parse($rootUrl);

    $contents = file_get_contents($rootUrl);
    $html = str_get_html($contents);

    foreach ($linkSelectors as $linkSel) {
      $links = $html->find($linkSel);

      if (empty($links)) {
        Log::warning("link selector [{$linkSel}] returns no matches on site {$siteId}");
      }

      foreach ($links as $link) {
        $articleUrl = $base->join($link->href);
        $articleDomain = parse_url($articleUrl, PHP_URL_HOST);

        if ($articleDomain == $rootDomain) { // skip cross-domain links
          $rec = CrawlerUrl::get_by_url($articleUrl);
          if (!$rec) {
            fetch($articleUrl, $siteId, $vars, $config['global']['path']);
          }
        }
      }
    }
  }
}

/*************************************************************************/

class CrawlerException extends Exception {
}

function fetch($url, $siteId, $vars, $path) {
  Log::info('fetching %s', urldecode($url));

  $cu = null;
  try {

    $cu = CrawlerUrl::create($url, $siteId);
    $cu->fetchAndExtract($vars['authorSelector'], $vars['authorRegexp'], $vars['titleSelector'],
                         $vars['bodySelector']);
    $cu->save();

    Log::info("saved as CrawlerUrl ID # {$cu->id}");

  } catch (CrawlerException $e) {
    Log::warning('crawler exception: %s', $e->getMessage());
  }

  if ($cu->id) {
    try {
      $cu->saveHtml($path);
      $cu->saveBody($path);
    } catch (CrawlerException $e) {
      Log::critical('crawler exception: %s', $e->getMessage());
      exit;
    }
  }

  sleep(2);
}
