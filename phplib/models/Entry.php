<?php

class Entry extends BaseObject implements DatedObject {
  public static $_table = 'Entry';

  private $lexems = null;

  static function createAndSave($description) {
    $e = Model::factory('Entry')->create();
    $e->description = $description;
    $e->save();
    return $e;
  }

  function getLexems() {
    if ($this->lexems == null) {
      $this->lexems = Lexem::get_all_by_entryId($this->id);
    }
    return $this->lexems;
  }

  function getLexemIds() {
    $result = [];
    foreach ($this->getLexems() as $l) {
      $result[] = $l->id;
    }
    return $result;
  }

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
