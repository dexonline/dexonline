<?php

class Footnote {

  private $html;
  private $userId;

  function __construct($html, $userId) {
    $this->html = $html;
    $this->userId = $userId;
  }

  function getHtml() {
    return $this->html;
  }

  function getUser() {
    return User::get_by_id($this->userId);
  }
}
