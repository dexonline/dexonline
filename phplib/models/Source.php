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

  public static $UNKNOWN_DEF_COUNT = -1.0;
  /**
   * percentComplete has a special value of UNKNOWN when the defCount is unknown
   **/
  public static $UNKNOWN_PERCENT = -1.0;

  public function getTypeName() {
    return self::$TYPE_NAMES[$this->type];
  }

  public function updatePercentComplete() {
    switch ($this->defCount) {
    case self::$UNKNOWN_DEF_COUNT: $this->percentComplete = self::$UNKNOWN_PERCENT; break;
    case 0: $this->percentComplete = 0; break;
    default: $this->percentComplete = min(100 * $this->ourDefCount / $this->defCount, 100);
    }
  }

  public function isUnknownPercentComplete() {
    return $this->percentComplete == self::$UNKNOWN_PERCENT;
  }
}

?>
