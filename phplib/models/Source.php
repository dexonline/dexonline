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

  public static $UNKNOWN_DEF_COUNT = -1.0;
  /**
   * percentComplete has a special value of UNKNOWN when the defCount is unknown
   **/
  public static $UNKNOWN_PERCENT = -1.0;

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
}

?>
