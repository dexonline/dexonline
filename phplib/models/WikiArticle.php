<?php

class WikiArticle extends BaseObject implements DatedObject {
  public static $_table = 'WikiArticle';

  public function extractKeywords() {
    $result = array();

    // Capture the {{CuvinteCheie|...}} template
    $matches = array();
    preg_match_all('/\{\{CuvinteCheie\|([^\}]+)\}\}/', $this->wikiContents, $matches);
    foreach ($matches[1] as $match) {
      $parts = explode(',', $match);
      foreach ($parts as $part) {
        $result[] = mb_strtolower(trim($part));
      }
    }

    // Capture the {{cc|...}} and {{ccd|...}} templates
    preg_match_all('/\{\{ccd?\|([^\}]+)\}\}/i', $this->wikiContents, $matches);
    foreach ($matches[1] as $match) {
      $parts = explode('|', $match);
      $result[] = mb_strtolower(trim($parts[0]));
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
      ->raw_query("select WikiArticle.* from WikiArticle, WikiKeyword where WikiArticle.id = WikiKeyword.wikiArticleId and WikiKeyword.keyword in ($lexemConcat)")
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
