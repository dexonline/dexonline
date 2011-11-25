<?php

class Source extends BaseObject {
  public static $_table = 'Source';

  public static $UNKNOWN_DEF_COUNT = -1.0;
  /**
   * percentComplete has a special value of UNKNOWN when the defCount is unknown
   **/
  public static $UNKNOWN_PERCENT = -1.0;

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
