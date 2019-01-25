<?php

class Source extends BaseObject implements DatedObject {
  public static $_table = 'Source';

  const TYPE_UNOFFICIAL = 0;
  const TYPE_SPECIALIZED = 1;
  const TYPE_OFFICIAL = 2;
  const TYPE_HIDDEN = 3;

  public static $TYPE_NAMES = [
    self::TYPE_UNOFFICIAL  => 'neoficial',
    self::TYPE_SPECIALIZED  => 'specializat',
    self::TYPE_OFFICIAL  => 'oficial',
    self::TYPE_HIDDEN  => 'ascuns',
  ];

  const IMPORT_TYPE_MIXED = 0;
  const IMPORT_TYPE_MANUAL = 1;
  const IMPORT_TYPE_OCR = 2;
  const IMPORT_TYPE_SCRIPT = 3;

  public static $IMPORT_TYPE_LABELS = [
    self::IMPORT_TYPE_MIXED => 'nedefinit',
    self::IMPORT_TYPE_MANUAL => 'manual',
    self::IMPORT_TYPE_OCR => 'via OCR',
    self::IMPORT_TYPE_SCRIPT => 'automat (script)',
  ];

  const SORT_DISPLAY = 0;
  const SORT_SEARCH = 1;
  const SORT_SHORT_NAME = 2;

  private static $SORT_CRITERIA = [
    // prefer the drag-and-drop order in /surse.php
    self::SORT_DISPLAY => [ 'displayOrder asc' ],

    // prefer the search form favorites in the dropdownOrder field
    self::SORT_SEARCH => [ 'dropdownOrder desc', 'displayOrder asc' ],

    self::SORT_SHORT_NAME => [ 'shortName asc' ],
  ];

  public static $UNKNOWN_DEF_COUNT = -1.0;
  /**
   * percentComplete has a special value of UNKNOWN when the defCount is unknown
   **/
  public static $UNKNOWN_PERCENT = -1.0;

  // glyphs expected to be common in all sources
  const BASE_GLYPHS =
    'aăâbcdefghiîjklmnopqrsștțuvwxyz' . // lowercase letters
    'AĂÂBCDEFGHIÎJKLMNOPQRSȘTȚUVWXYZ' . // uppercase letters
    '0123456789' .                      // digits
    '@#$%^' .                           // formatting
    '.,;:-()' .                         // punctuation
    "\\\n ";                            // other

  function getTypeName() {
    return self::$TYPE_NAMES[$this->type];
  }

  function getImportTypeLabel() {
    return self::$IMPORT_TYPE_LABELS[$this->importType];
  }

  function updatePercentComplete() {
    switch ($this->defCount) {
    case self::$UNKNOWN_DEF_COUNT: $this->percentComplete = self::$UNKNOWN_PERCENT; break;
    case 0: $this->percentComplete = 0; break;
    default: $this->percentComplete = min(100 * $this->ourDefCount / $this->defCount, 100);
    }
  }

  function isUnknownPercentComplete() {
    return $this->percentComplete == self::$UNKNOWN_PERCENT;
  }

  static function getSourcesWithPageImages() {
    return Model::factory('Source')
      ->where('hasPageImages', 1)
      ->order_by_desc('dropdownOrder')
      ->order_by_asc('displayOrder')
      ->find_many();
  }

  static function getAll($sort = self::SORT_DISPLAY) {
    $query = Model::factory('Source');
    foreach (self::$SORT_CRITERIA[$sort] as $expr) {
      $query = $query->order_by_expr($expr);
    }
    return $query->find_many();
  }
}
