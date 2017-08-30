<?php

require_once __DIR__ . '/../../phplib/Core.php';
require_once __DIR__ . '/../../phplib/third-party/simple_html_dom.php';
require_once __DIR__ . '/../../phplib/third-party/phpuri.php';

$config = parse_ini_file('crawler.conf', true);

foreach ($config as $section => $vars) {
  if (StringUtil::startsWith($section, 'site-')) {
    $siteId = explode('-', $section, 2)[1];
    $rootUrl = $vars['url'];
    $linkSelectors = $vars['articleLinkSelector'];
    Log::info("crawling site: {$siteId}, root URL: {$rootUrl}");

    // for converting relative URLs to absolute
    $base = phpUri::parse($rootUrl);

    $contents = file_get_contents($rootUrl);
    $html = str_get_html($contents);

    foreach ($linkSelectors as $linkSel) {
      $links = $html->find($linkSel);

      foreach ($links as $link) {
        $articleUrl = $base->join($link->href);

        $rec = CrawlerUrl::get_by_url($articleUrl);
        if (!$rec) {
          fetch($articleUrl, $siteId, $vars, $config['global']['path']);
        }
      }
    }
  }
}

/*************************************************************************/

class CrawlerException extends Exception {
}

function fetch($url, $siteId, $vars, $path) {
  Log::info("fetching {$url}");

  try {

    $cu = CrawlerUrl::create($url, $siteId);
    $cu->fetchAndExtract($vars['authorSelector'], $vars['authorRegexp'], $vars['titleSelector'],
                         $vars['bodySelector']);
    $cu->save();
    $cu->saveHtml($path);
    $cu->saveBody($path);

  } catch (CrawlerException $e) {
    Log::warning('crawler exception: %s', $e->getMessage());
  }

  sleep(2);
}
