<?php

class Entry extends BaseObject implements DatedObject {
  public static $_table = 'Entry';

  /**
   * Validates an entry for correctness. Returns an array of { field => array of errors }.
   **/
  function validate() {
    $errors = [];

    if (!mb_strlen($this->description)) {
      $errors['description'][] = _('Descrierea nu poate fi vidă.');
    }

    return $errors;
  }

}

?>
