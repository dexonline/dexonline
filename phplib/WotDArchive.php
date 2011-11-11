<?php

class WotDArchive {
  public $displayDate;
  public $lexicon;
  public $linkDate;
  public $dayOfWeek;
  public $dayOfMonth;

  public function set($record) {
    $this->displayDate = $record['displayDate'];
    $this->lexicon = $record['lexicon'];
    $this->linkDate = $record['linkDate'];
    $this->dayOfWeek= $record['dayOfWeek'];
    $this->dayOfMonth= $record['dayOfMonth'];
  }

  public static function setOnlyDate($date) {
    $obj = new WotDArchive();
    $obj->displayDate = $date;
    $obj->lexicon = '';
    $obj->linkDate = '';
    $dow = date('N', strtotime($date));
    $obj->dayOfWeek = ($dow == 7) ? 1 : $dow+1;
    $obj->dayOfMonth = date('j', strtotime($date));
    return $obj;
  }
}

?>
