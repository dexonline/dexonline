<?php

class BaseObject extends ADOdb_Active_Record {
  public function save() {
    if ($this->createDate === null) {
      $this->createDate = $this->modDate = time();
    }
    if (is_string($this->modDate)) {
      $this->modDate = time();
    }
    parent::save();
  }
}

?>
