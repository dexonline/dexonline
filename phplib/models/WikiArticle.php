<?php

class WikiArticle extends BaseObject {
  public static $_table = 'WikiArticle';

  public function extractKeywords() {
    $matches = array();
    if (!preg_match_all('/\{\{CuvinteCheie\|([^\}]+)\}\}/', $this->wikiContents, $matches)) {
      return array();
    }
    $result = array();
    foreach ($matches[1] as $match) {
      $parts = explode(',', $match);
      foreach ($parts as $part) {
        $result[] = mb_strtolower(trim($part));
      }
    }
    return $result;
  }

  public function getUrlTitle() {
    return WikiArticle::wikiTitleToUrlTitle($this->title);
  }

  public static function urlTitleToWikiTitle($urlTitle) {
    return str_replace('_', ' ', $urlTitle);
  }

  public static function wikiTitleToUrlTitle($wikiTitle) {
    return urlencode(str_replace(' ', '_', $wikiTitle));
  }

  public static function loadAllTitlesOld() {
    $titles = db_getArray("select title from WikiArticle order by title");
    $result = array();
    foreach ($titles as $title) {
      $result[] = array($title, WikiArticle::wikiTitleToUrlTitle($title));
    }
    return $result;
  }

  public static function loadAllTitles() {
    $rows = db_getArrayOfRows("select section, title from WikiArticle left join WikiSection using (pageId) order by section, title");
    $result = array();
    foreach ($rows as $row) {
      $result[$row['section']][] = array($row['title'], WikiArticle::wikiTitleToUrlTitle($row['title']));
    }
    return $result;
  }

  public static function loadForLexems($lexems) {
    if (!count($lexems)) {
      return array();
    }
    $lexemForms = array();
    foreach ($lexems as $l) {
      $lexemForms[] = "'{$l->formNoAccent}'"; 
    }
    $lexemConcat = implode(', ', $lexemForms);
    return Model::factory('WikiArticle')
      ->raw_query("select WikiArticle.* from WikiArticle, WikiKeyword where WikiArticle.id = WikiKeyword.wikiArticleId and WikiKeyword.keyword in ($lexemConcat)", null)
      ->find_many();
  }

  public static function getRss() {
    return Model::factory('WikiArticle')->order_by_desc('modDate')->limit(25)->find_many();
  }

  public function delete() {
    WikiKeyword::deleteByWikiArticleId($this->id);
    parent::delete();
  }
}

?>
