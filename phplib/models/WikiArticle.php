<?php

class WikiArticle extends BaseObject implements DatedObject {
  public static $_table = 'WikiArticle';

  function extractKeywords() {
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

  function getUrlTitle() {
    return urlencode(str_replace(' ', '_', $this->title));
  }

  static function urlTitleToWikiTitle($urlTitle) {
    return str_replace('_', ' ', $urlTitle);
  }

  // returns article titles grouped by section
  static function loadAllTitles() {
    $was = Model::factory('WikiArticle')
         ->select('section')
         ->select('title')
         ->order_by_asc('section')
         ->order_by_asc('title')
         ->find_many();
    $result = [];
    foreach ($was as $wa) {
      $result[$wa->section][] = $wa;
    }
    return $result;
  }

  static function loadForEntries($entries) {
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
      ->join('Lexeme', ['wk.keyword', '=', 'l.formNoAccent'], 'l')
      ->join('EntryLexeme', ['l.id', '=', 'el.lexemeId'], 'el')
      ->where_in('el.entryId', $entryIds)
      ->find_many();
  }

  static function getRss() {
    return Model::factory('WikiArticle')->order_by_desc('modDate')->limit(25)->find_many();
  }

  function delete() {
    WikiKeyword::deleteByWikiArticleId($this->id);
    parent::delete();
  }
}
