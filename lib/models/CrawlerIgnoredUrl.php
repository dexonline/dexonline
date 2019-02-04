<?php

/**
 * We mark URLs as ignored when we repeatedly fail to crawl or parse them: either they are unavailable
 * or they are not formatted as articles.
 **/

class CrawlerIgnoredUrl extends BaseObject implements DatedObject {
  static $_table = 'CrawlerIgnoredUrl';

  // mark the URL as ignored and stop trying to crawl it after this many failures
  const RETRY_THRESHOLD = 3;

  static function isIgnored($url) {
    $rec = Model::factory('CrawlerIgnoredUrl')
         ->where('url', $url)
         ->where_gte('failureCount', self::RETRY_THRESHOLD)
         ->find_one();
    return ($rec != null);
  }

  // create or increment the failure count
  static function incrementFailures($url) {
    $i = CrawlerIgnoredUrl::get_by_url($url);
    if (!$i) {
      $i = Model::factory('CrawlerIgnoredUrl')->create();
      $i->url = $url;
    }
    $i->failureCount++;
    $i->save();
  }
}
