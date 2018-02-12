<?php

class Footnote extends BaseObject implements DatedObject {
  public static $_table = 'Footnote';

  function getUser() {
    return User::get_by_id($this->userId);
  }
}
