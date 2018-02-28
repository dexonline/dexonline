<?php

/**
 * Extract unknown words from crawled articles.
 **/

require_once __DIR__ . '/../../phplib/Core.php';

$config = parse_ini_file('crawler.conf', true);
$root = $config['global']['path'];

define('BATCH_SIZE', 100);
$offset = 0;

do {
  $cus = Model::factory('CrawlerUrl')
       ->where('extractedUnknownWords', false)
       ->order_by_asc('id')
       ->limit(BATCH_SIZE)
       ->offset($offset)
       ->find_many();

  foreach ($cus as $cu) {
    $cu->extractUnknownWords();
  }

  $offset += count($cus);
  Log::info("Processed $offset crawled URLs.");
} while (count($cus) == BATCH_SIZE);
