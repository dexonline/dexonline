<?php

/**
 * Split articles into phrases
 **/

require_once __DIR__ . '/../../phplib/Core.php';

$config = parse_ini_file('crawler.conf', true);
$root = $config['global']['path'];

const BATCH_SIZE = 100;
$offset = 0;

do {
  $cus = Model::factory('CrawlerUrl')
       ->where_raw('id not in (select crawlerUrlId from CrawlerPhrase)')
       ->order_by_asc('id')
       ->limit(BATCH_SIZE)
       ->offset($offset)
       ->find_many();

  foreach ($cus as $cu) {
    $cu->loadBody($root);
    $phrases = $cu->getPhrases();
    foreach ($phrases as $p) {
      $cp = Model::factory('CrawlerPhrase')->create();
      $cp->crawlerUrlId = $cu->id;
      $cp->contents = $p;
      $cp->save();
    }
    Log::info('split %d phrases from article [%d] [%s]',
              count($phrases), $cu->id, $cu->url);
  }

  $offset += count($cus);
  Log::info("Processed $offset crawled URLs.");
} while (count($cus) == BATCH_SIZE);
