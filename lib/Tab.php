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
  const T_PRONUNCIATION = 5;

  /**
   * The default order of tabs. Can be overridden by user preferences.
   */
  const ORDER = [
    self::T_TREES,
    self::T_RESULTS,
    self::T_PARADIGM,
    self::T_GALLERY,
    self::T_ARTICLES,
    self::T_PRONUNCIATION,
  ];

  const URL = [
    self::T_RESULTS => '/definitii',
    self::T_PARADIGM => '/paradigma',
    self::T_TREES => '/sinteza',
    self::T_GALLERY => '/imagini',
    self::T_ARTICLES => '/articole',
    self::T_PRONUNCIATION => '/pronuntie',
  ];

  const PROMINENT = [
    self::T_RESULTS => false,
    self::T_PARADIGM => false,
    self::T_TREES => false,
    self::T_GALLERY => true,
    self::T_ARTICLES => true,
    self::T_PRONUNCIATION => false,
  ];

  const EMPHASIZE = [
    self::T_RESULTS => false,
    self::T_PARADIGM => false,
    self::T_TREES => false,
    self::T_GALLERY => false,
    self::T_ARTICLES => false,
    self::T_PRONUNCIATION => true,
  ];

  /**
   * Returns a localized name for the given tab.
   */
  static function getName(int $tab) {
    switch ($tab) {
      case self::T_RESULTS:  return _('definitions');
      case self::T_PARADIGM: return _('conjugations') . ' / ' . _('declensions');
      case self::T_TREES:    return _('synthesis');
      case self::T_GALLERY:  return _('images');
      case self::T_ARTICLES: return _('articles');
      case self::T_PRONUNCIATION: return 'pronunție';
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

  static function getDefaultOrderPosition($tab) {
    return array_search($tab, self::ORDER);
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
   * Based on the URL, user preferences and the availability of data, returns
   * an ordered list of tabs to display and the active tab.
   *
   * @param mixed $urlTab One of the T_* constans or false if the URL doesn't indicate a tab.
   * @param int $numResults The number of items on the definitions tab.
   * @param bool $hasParadigm Whether we should display the paradigm tab.
   * @param int $numTrees The number of structured trees.
   * @param int $numImages The number of images.
   * @param int $numArticles The number of linguistics articles.
   * @param string $declensionText The title of the paradigm tab.
   *
   * @return A tuple of
   *   * the active tab
   *   * an array of tab ID => tab info
   */
  static function getInfo(
    $urlTab, int $numResults, bool $hasParadigm, int $numTrees, int $numImages,
    int $numArticles, string $declensionText) {

    $allTabs = Session::getTabs();

    $isVisible = [
      self::T_RESULTS => true, // this tab is always visible
      self::T_PARADIGM => $hasParadigm,
      self::T_TREES => $numTrees,
      self::T_GALLERY => $numImages,
      self::T_ARTICLES => $numArticles,
      self::T_PRONUNCIATION => true,
    ];
    $counts = [
      self::T_RESULTS => $numResults,
      self::T_PARADIGM => 0, // never display this count
      self::T_TREES => $numTrees,
      self::T_GALLERY => $numImages,
      self::T_ARTICLES => $numArticles,
      self::T_PRONUNCIATION => 0, // never display this count
    ];

    $tabs = [];
    foreach ($allTabs as $tab) {
      if ($isVisible[$tab]) {
        $tabs[$tab] = [
          'count' => $counts[$tab],
          'prominent' => self::PROMINENT[$tab],
          'emphasize' => self::EMPHASIZE[$tab],
          'title' => ($tab == self::T_PARADIGM) ? $declensionText : self::getName($tab),
          'icon' => ($tab == self::T_PRONUNCIATION) ? 'volume_up' : null,
        ];
      }
    }

    $activeTab = (($urlTab !== false) && ($isVisible[$urlTab]))
      ? $urlTab
      : array_key_first($tabs);

    return [ $activeTab, $tabs ];
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
