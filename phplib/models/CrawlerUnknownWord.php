<?php

class CrawlerUnknownWord extends BaseObject implements DatedObject {
  static $_table = 'CrawlerUnknownWord';

  /**
   * Extracts contexts containing this word from any CrawledUrl (not
   * necessarily only from $this->crawlerUrlId).
   *
   * @param int $limit Load at most this many examples.
   * @return array For each example, returns a pair of the CrawlerUrl and a
   * string extracted from that article.
   **/
  function loadExamples($limit) {
    $uws = Model::factory('CrawlerUnknownWord')
         ->where('word', $this->word)
         ->order_by_expr('rand()')
         ->limit($limit)
         ->find_many();

    $results = [];
    foreach ($uws as $uw) {
      $u = CrawlerUrl::get_by_id($uw->crawlerUrlId);
      $u->loadBody();
      $c = $u->getContext($uw);
      $results[] = [
        'crawlerUrl' => $u,
        'context' => $c,
      ];
    }

    return $results;
  }
}
