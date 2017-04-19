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
    return self::wikiTitleToUrlTitle($this->title);
  }

  public static function urlTitleToWikiTitle($urlTitle) {
    return str_replace('_', ' ', $urlTitle);
  }

  public static function wikiTitleToUrlTitle($wikiTitle) {
    return urlencode(str_replace(' ', '_', $wikiTitle));
  }

  public static function loadAllTitles() {
    $rows = DB::getArrayOfRows("select section, title from WikiArticle left join WikiSection using (pageId) order by section, title");
    $result = array();
    foreach ($rows as $row) {
      $result[$row['section']][] = array($row['title'], WikiArticle::wikiTitleToUrlTitle($row['title']));
    }
    return $result;
  }

  public static function loadForEntries($entries) {
    if (!count($entries)) {
      return [];
    }
    $entryIds = Util::objectProperty($entries, 'id');

    return Model::factory('WikiArticle')
      ->table_alias('wa')
      ->select('wa.id')
      ->select('wa.title')
      ->distinct()
      ->join('WikiKeyword', ['wa.id', '=', 'wk.wikiArticleId'], 'wk')
      ->join('Lexem', ['wk.keyword', '=', 'l.formNoAccent'], 'l')
      ->join('EntryLexem', ['l.id', '=', 'el.lexemId'], 'el')
      ->where_in('el.entryId', $entryIds)
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
