<?php

class Entry extends BaseObject implements DatedObject {
  public static $_table = 'Entry';

  private $lexems = null;
  private $trees = null;

  static function createAndSave($description) {
    $e = Model::factory('Entry')->create();
    $e->description = $description;
    $e->save();
    return $e;
  }

  function getLexems() {
    if ($this->lexems === null) {
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

  function getTrees() {
    if ($this->trees === null) {
      $this->trees = Model::factory('Tree')
                   ->table_alias('t')
                   ->select('t.*')
                   ->join('TreeEntry', ['te.treeId', '=', 't.id'], 'te')
                   ->where('te.entryId', $this->id)
                   ->find_many();
    }
    return $this->trees;
  }

  function getTreeIds() {
    $result = [];
    foreach ($this->getTrees() as $t) {
      $result[] = $t->id;
    }
    return $result;
  }

  function loadMeanings() {
    foreach ($this->getTrees() as $t) {
      $t->getMeanings();
    }
  }

  public static function countUnassociated() {
    // We compute this as (all entries) - (entries showing up in EntryDefinition)
    $all = Model::factory('Entry')->count();
    $associated = db_getSingleValue('select count(distinct entryId) from EntryDefinition');
    return $all - $associated;
  }

  public static function loadUnassociated() {
    return Model::factory('Entry')
      ->raw_query('select * from Entry where id not in (select entryId from EntryDefinition) order by description')
      ->find_many();
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

  public function delete() {
    EntryDefinition::deleteByEntryId($this->id);
    TreeEntry::delete_all_by_entryId($this->id);

    // do not delete the lexems for now -- just orphan them
    $lexems = Lexem::get_all_by_entryId($this->id);
    foreach ($lexems as $l) {
      Log::info("Orphaned lexem {$l}");
      $l->entryId = null;
      $l->save();
    }

    Log::warning("Deleted entry {$this->id} ({$this->description})");
    parent::delete();
  }

}

?>
