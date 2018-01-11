<?php

/**
 * All sorts of cleanup and sanity checks for existing CrawlerUrls.
 **/

require_once __DIR__ . '/../../phplib/Core.php';
require_once __DIR__ . '/../../phplib/third-party/simple_html_dom.php';
require_once __DIR__ . '/../../phplib/third-party/phpuri.php';

// build a map of domains to site IDs
$urlMap = [];

$config = parse_ini_file('crawler.conf', true);
foreach ($config as $section => $vars) {
  if (Str::startsWith($section, 'site-')) {
    $siteId = explode('-', $section, 2)[1];
    $rootUrl = $vars['url'];
    $rootDomain = parse_url($rootUrl, PHP_URL_HOST);
    $urlMap[$rootDomain] = $siteId;
  }
}

$root = $config['global']['path'];

define('BATCH_SIZE', 100);
$offset = 0;

do {
  $cus = Model::factory('CrawlerUrl')
       ->order_by_asc('id')
       ->limit(BATCH_SIZE)
       ->offset($offset)
       ->find_many();

  foreach ($cus as $cu) {
    $domain = parse_url($cu->url, PHP_URL_HOST);
    $cu->loadBody($root);
    $cu->loadHtml($root);
  }

  $offset += count($cus);
  Log::info("Processed $offset crawled URLs.");
} while (count($cus));
