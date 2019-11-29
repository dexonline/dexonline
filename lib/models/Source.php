<?php

class Source extends BaseObject implements DatedObject {
  public static $_table = 'Source';

  const TYPE_UNOFFICIAL = 0;
  const TYPE_SPECIALIZED = 1;
  const TYPE_OFFICIAL = 2;

  const TYPE_NAMES = [
    self::TYPE_UNOFFICIAL  => 'neoficial',
    self::TYPE_SPECIALIZED  => 'specializat',
    self::TYPE_OFFICIAL  => 'oficial',
  ];

  const IMPORT_TYPE_MIXED = 0;
  const IMPORT_TYPE_MANUAL = 1;
  const IMPORT_TYPE_OCR = 2;
  const IMPORT_TYPE_SCRIPT = 3;

  const IMPORT_TYPE_LABELS = [
    self::IMPORT_TYPE_MIXED => 'nedefinit',
    self::IMPORT_TYPE_MANUAL => 'manual',
    self::IMPORT_TYPE_OCR => 'via OCR',
    self::IMPORT_TYPE_SCRIPT => 'automat (script)',
  ];

  const SORT_DISPLAY = 0;
  const SORT_SEARCH = 1;
  const SORT_SHORT_NAME = 2;

  const SORT_CRITERIA = [
    // prefer the drag-and-drop order in the source list
    self::SORT_DISPLAY => [ 'displayOrder asc' ],

    // prefer the search form favorites in the dropdownOrder field
    self::SORT_SEARCH => [ 'dropdownOrder desc', 'displayOrder asc' ],

    self::SORT_SHORT_NAME => [ 'shortName asc' ],
  ];

  const UNKNOWN_DEF_COUNT = -1.0;
  /**
   * percentComplete has a special value of UNKNOWN when the defCount is unknown
   **/
  const UNKNOWN_PERCENT = -1.0;

  // glyphs expected to be common in all sources
  const BASE_GLYPHS =
    'aăâbcdefghiîjklmnopqrsștțuvwxyz' . // lowercase letters
    'AĂÂBCDEFGHIÎJKLMNOPQRSȘTȚUVWXYZ' . // uppercase letters
    '0123456789' .                      // digits
    '@#$%^' .                           // formatting
    '.,;:-()' .                         // punctuation
    "\\\n ";                            // other

  function getImportTypeLabel() {
    return self::IMPORT_TYPE_LABELS[$this->importType];
  }

  function getPublisherDetails() {
    $details = [];
    if ($this->publisher) {
      $details[] = $this->publisher;
    }
    if ($this->year) {
      $details[] = $this->year;
    }
    return implode(', ', $details);
  }

  /**
   * Returns this Source's authors ordered by rank.
   *
   * @return SourceAuthor[]
   */
  function getAuthors() {
    return Model::factory('SourceAuthor')
      ->where('sourceId', $this->id)
      ->order_by_asc('rank')
      ->find_many();
  }

  /**
   * Returns this Source's authors mapped by their role. Roles are sorted in
   * increasing order of priority.
   *
   * @return SourceAuthor[][]
   */
  function getAuthorMap() {
    $authors = Model::factory('SourceAuthor')
      ->table_alias('sa')
      ->select('sa.*')
      ->join('SourceRole', ['sa.sourceRoleId', '=', 'sr.id'], 'sr')
      ->where('sa.sourceId', $this->id)
      ->order_by_asc('sr.priority')
      ->order_by_asc('sa.rank')
      ->find_many();
    $results = [];

    foreach ($authors as $a) {
      $results[$a->sourceRoleId]['authors'][] = $a;
    }

    foreach ($results as $sourceRoleId => &$rec) {
      $rec['role'] = SourceRole::get_by_id($sourceRoleId);
    }

    return $results;
  }

  function updatePercentComplete() {
    switch ($this->defCount) {
      case self::UNKNOWN_DEF_COUNT: $this->percentComplete = self::UNKNOWN_PERCENT; break;
      case 0: $this->percentComplete = 0; break;
      default: $this->percentComplete = min(100 * $this->ourDefCount / $this->defCount, 100);
    }
  }

  function isUnknownPercentComplete() {
    return $this->percentComplete == self::UNKNOWN_PERCENT;
  }

  static function getSourcesWithPageImages() {
    return Model::factory('Source')
      ->where('hasPageImages', 1)
      ->order_by_desc('dropdownOrder')
      ->order_by_asc('displayOrder')
      ->find_many();
  }

  /**
   * Returns sources
   *
   * @param function name of function to be called
   * @param mixed    values from self::SORT_CRITERIA
   * @return ORMWrapper
   */
  function get($function, $sort = self::SORT_DISPLAY) {
    if (method_exists($this, $function)) {
      return call_user_func_array(array($this, $function), $sort);
    }

    return null;
  }

  /**
   * Returns all sources a user can see, based on his privilege
   *
   * @param mixed values from self::SORT_CRITERIA
   * @return ORMWrapper
   */
  static function getAll($sort = self::SORT_DISPLAY) {
    $query = Model::factory(static::$_table);
    if (!User::can(User::PRIV_VIEW_HIDDEN)) {
      $query = $query->where('hidden', false);
    }
    self::setSortOrder($query, $sort);

    return $query->find_many();
  }

  /**
   * Returns all sources a user can see, based on his privilege
   *
   * @param mixed values from self::SORT_CRITERIA
   * @return ORMWrapper
   */
  static function getAllCanModerate($sort = self::SORT_DISPLAY) {
    $query = Model::factory(static::$_table)
      ->where('canModerate', true);
    self::setSortOrder($query, $sort);

    return $query->find_many();
  }

  /**
   * Returns all sources that have at least one definition with a typo
   *
   * @param mixed values from self::SORT_CRITERIA
   * @return ORMWrapper
   */
  static function getAllForTypos($sort = self::SORT_DISPLAY) {
    $query = Model::factory(static::$_table)
      ->table_alias('s')
      ->select('s.*')
      ->distinct()
      ->join('Definition', [ 'd.sourceId', '=', 's.id'], 'd')
      ->join('Typo', [ 't.definitionId', '=', 'd.id'], 't');
    self::setSortOrder($query, $sort);

    return $query->find_many();
  }

  /**
   * Returns all sources without images
   *
   * @param mixed values from self::SORT_CRITERIA
   * @return ORMWrapper
   */
  static function getAllWithoutPageImages($sort = self::SORT_DISPLAY) {
    $query = Model::factory(static::$_table)
      ->where('hasPageImages', false);
    self::setSortOrder($query, $sort);

    return $query->find_many();
  }

  /**
   * Returns all sources with images
   *
   * @param mixed values from self::SORT_CRITERIA
   * @return ORMWrapper
   */
  static function getAllForPageImages($sort = self::SORT_DISPLAY) {
    $query = Model::factory(static::$_table)
      ->where('hasPageImages', true);
    self::setSortOrder($query, $sort);

    return $query->find_many();
  }

  /**
   * Returns all sources that have abbreviations defined
   *
   * @param mixed values from self::SORT_CRITERIA
   * @return ORMWrapper
   */
  static function getAllForAbbreviations($sort = self::SORT_DISPLAY) {
    $query = Model::factory(static::$_table)
      ->table_alias('s')
      ->select('s.*')
      ->distinct()
      ->join('Abbreviation', [ 'a.sourceId', '=', 's.id'], 'a');
    self::setSortOrder($query, $sort);

    return $query->find_many();
  }

  /**
   * Returns all sources that have at last one definition unmarked with rare glyphs tag
   *
   * @param mixed values from self::SORT_CRITERIA
   * @return ORMWrapper
   */
  static function getAllForRareGlyphTags($sort = self::SORT_DISPLAY) {
    $join = sprintf('(d.id = ot.objectId) and (ot.objectType = %d) and (ot.tagId = %d)',
      ObjectTag::TYPE_DEFINITION,
      Config::TAG_ID_RARE_GLYPHS);
    $query = Model::factory(static::$_table)
      ->table_alias('s')
      ->select('s.*')
      ->distinct()
      ->join('Definition', [ 'd.sourceId', '=', 's.id'], 'd')
      ->left_outer_join('ObjectTag', $join, 'ot')
      ->where_not_equal('d.rareGlyphs', '')
      ->where_null('ot.id');
    self::setSortOrder($query, $sort);

    return $query->find_many();
  }

  private static function setSortOrder(&$query, $sort) {
    foreach (self::SORT_CRITERIA[$sort] as $expr) {
      $query = $query->order_by_expr($expr);
    }
  }

  static function getBaseGlyphsDisplay() {
    $s = preg_replace('/a.*z/', 'a-z, ', self::BASE_GLYPHS);
    $s = preg_replace('/A.*Z/', 'A-Z, ', $s);
    $s = preg_replace('/0.*9/', '0-9, ', $s);
    return $s;
  }
}
