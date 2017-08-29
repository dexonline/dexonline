<?php

require_once __DIR__ . '/../../phplib/Core.php';
require_once __DIR__ . '/../../phplib/third-party/simple_html_dom.php';

$config = parse_ini_file('crawler.conf', true);

foreach ($config as $section => $vars) {
  if (StringUtil::startsWith($section, 'root-site')) {
    $url = $vars['url'];
    $linkSel = $vars['articleLinkSelector'];

    $contents = file_get_contents($url);
    $indexHtml = str_get_html($indexContents);
    $links = $html->find($sel);

    foreach ($links as $link) {
      // TODO download
      var_dump($link->href);
    }
  }
}
