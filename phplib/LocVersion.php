<?php

class LocVersion {
  public $name;
  public $freezeTimestamp;

  private function getUrl($type) {
    return sprintf('%sdownload/scrabble/loc-%s-%s.zip',
                   Config::get('static.url'), $type, $this->name);
  }

  function getBaseFormUrl() {
    return $this->getUrl('baza');
  }

  function getInflectedFormUrl() {
    return $this->getUrl('flexiuni');
  }

  function getReducedFormUrl() {
    return $this->getUrl('reduse');
  }

}
