<?php

class Footnote {

  public $internalRep;
  public $sourceId;
  private $userId;

  function __construct($internalRep, $sourceId, $userId) {
    $this->internalRep = $internalRep;
    $this->sourceId = $sourceId;
    $this->userId = $userId;
  }

  function getUser() {
    return User::get_by_id($this->userId);
  }
}
