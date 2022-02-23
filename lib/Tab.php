<?php

/**
 * This class handles tab names and URLs
 *   - in search results;
 *   - in the "tab order" section of the preferences page.
 **/
class Tab {

  const T_RESULTS = 0;
  const T_PARADIGM = 1;
  const T_TREES = 2;
  const T_GALLERY = 3;
  const T_ARTICLES = 4;

  /**
   * The default order of tabs. Can be overridden by user preferences.
   */
  const ORDER = [
    self::T_RESULTS,
    self::T_PARADIGM,
    self::T_TREES,
    self::T_GALLERY,
    self::T_ARTICLES,
  ];

  const URL = [
    self::T_RESULTS => '/definitii',
    self::T_PARADIGM => '/paradigma',
    self::T_TREES => '/sinteza',
    self::T_GALLERY => '/imagini',
    self::T_ARTICLES => '/articole',
  ];

  /**
   * Returns a localized name for the given tab.
   */
  static function getName(int $tab) {
    switch ($tab) {
      case self::T_RESULTS:  return _('results');
      case self::T_PARADIGM: return _('inflections');
      case self::T_TREES:    return _('synthesis');
      case self::T_GALLERY:  return _('images');
      case self::T_ARTICLES: return _('articles');
    }
  }

  /**
   * Returns the tab indicated by the URL.
   * @return int one of the T_* values or false if the URL does not indicate a tab.
   */
  static function getFromUrl() {
    return array_search('/' . Request::get('tab'), Tab::URL);
  }

  /**
   * Returns a permalink URL for the given tab, starting from the current page's URL.
   *
   * @param int $tab One of the T_* values.
   */
  static function getPermalink(int $tab) {
    $uri = $_SERVER['REQUEST_URI'];

    // remove existing tab markers
    $parts = implode('|', self::URL);
    $regexp = sprintf('@(%s)$@', $parts);
    $uri = preg_replace($regexp, '', $uri);

    // add the paradigm tab marker
    $uri .= self::URL[$tab];
    return $uri;
  }

  static function isDefaultOrder(array $tabs) {
    $n = count($tabs);
    $i = 0;
    while (($i < $n) && ($tabs[$i] == self::ORDER[$i])) {
      $i++;
    }
    return ($i == $n);
  }

  /**
   * Returns the tab that should be active on page render, based on the user
   * preferences and the availability of data.
   */
  static function getActive($hasParadigm, $hasTrees, $hasImages, $hasArticles) {
    $tabs = Session::getTabs();
    $good = [
      self::T_RESULTS => true, // this tab is always visible
      self::T_PARADIGM => $hasParadigm,
      self::T_TREES => $hasTrees,
      self::T_GALLERY => $hasImages,
      self::T_ARTICLES => $hasArticles,
    ];

    $i = 0;
    while (!$good[$tabs[$i]]) {
      $i++;
    }
    return $tabs[$i];
  }

  /**
   * Given a permutation of tabs, pack it on four bits each.
   * @return int
   */
  static function pack(array $tabs) {
    $result = 0;
    foreach ($tabs as $tab) {
      $result = ($result << 4) + $tab;
    }
    return $result;
  }

  /**
   * Opposite of pack().
   */
  static function unpack(int $x) {
    $tabs = [];
    $n = count(self::ORDER);
    while ($n--) {
      array_unshift($tabs, $x & 0xf);
      $x >>= 4;
    }
    return $tabs;
  }
}
