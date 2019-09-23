<?php

class Footnote {

  public $internalRep;
  public $sourceId;
  private $userId;

  function __construct($internalRep, $sourceId, $userId) {
    // The $internalRep was already partially passed through Str::htmlize(),
    // including htmlspecialchars(). Later when we pass the footnote through
    // HtmlConverter::convert(), the HTML entities will be escaped again. It
    // is complicated to prevent either of these and easier to decode them
    // once here.
    $this->internalRep = html_entity_decode($internalRep);
    $this->sourceId = $sourceId;
    $this->userId = $userId;
  }

  function getUser() {
    return User::get_by_id($this->userId);
  }
}
