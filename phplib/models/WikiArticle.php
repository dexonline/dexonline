<?php

class WikiArticle extends BaseObject {
  public static function get($where) {
    $obj = new WikiArticle();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }

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

  public static function loadAllTitles() {
    $titles = db_getArray(db_execute("select title from WikiArticle order by title"));
    $result = array();
    foreach ($titles as $title) {
      $result[] = array($title, WikiArticle::wikiTitleToUrlTitle($title));
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
    $query = "select WikiArticle.* from WikiArticle, WikiKeyword where WikiArticle.id = WikiKeyword.wikiArticleId and WikiKeyword.keyword in ($lexemConcat)";
    $dbResult = db_execute($query);
    return db_getObjects(new WikiArticle(), $dbResult);
  }

  public static function getRss() {
    $dbResult = db_execute("select * from WikiArticle order by modDate desc limit 25");
    return db_getObjects(new WikiArticle(), $dbResult);
  }


  public function delete() {
    WikiKeyword::deleteByWikiArticleId($this->id);
    parent::delete();
  }
}

?>
