<?php

class Entry extends BaseObject implements DatedObject {
  public static $_table = 'Entry';

  private $lexems = null;
  private $trees = null;

  const STRUCT_STATUS_NEW = 1;
  const STRUCT_STATUS_IN_PROGRESS = 2;
  const STRUCT_STATUS_UNDER_REVIEW = 3;
  const STRUCT_STATUS_DONE = 4;

  const STRUCTURIST_ID_ANY = -1;
  const STRUCTURIST_ID_NONE = 0;

  public static $STRUCT_STATUS_NAMES = [
    self::STRUCT_STATUS_NEW => 'neîncepută',
    self::STRUCT_STATUS_IN_PROGRESS => 'în lucru',
    self::STRUCT_STATUS_UNDER_REVIEW => 'așteaptă moderarea',
    self::STRUCT_STATUS_DONE => 'terminată',
  ];

  static function createAndSave($description) {
    $e = Model::factory('Entry')->create();
    $e->description = $description;
    $e->structStatus = self::STRUCT_STATUS_NEW;
    $e->save();
    return $e;
  }

  function _clone($cloneDefinitions, $cloneLexems, $cloneTrees) {
    $e = $this->parisClone();
    $e->description .= ' (CLONĂ)';
    $e->save();

    if ($cloneDefinitions) {
      EntryDefinition::copy($this->id, $e->id, 1);
    }

    if ($cloneLexems) {
      EntryLexem::copy($this->id, $e->id, 1);
    }

    if ($cloneTrees) {
      TreeEntry::copy($this->id, $e->id, 2);
    }

    return $e;
  }

  function getLexems() {
    if ($this->lexems === null) {
      $this->lexems = Model::factory('Lexem')
                    ->table_alias('l')
                    ->select('l.*')
                    ->join('EntryLexem', ['l.id', '=', 'el.lexemId'], 'el')
                    ->where('el.entryId', $this->id)
                    ->find_many();
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
                   ->order_by_asc('te.id')
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

  public static function loadUnassociated() {
    $query = 'select * from Entry ' .
           'where id not in (select entryId from EntryLexem) ' .
           'or id not in (select entryId from EntryDefinition)';
    return Model::factory('Entry')
      ->raw_query($query)
      ->find_many();
  }

  /**
   * Validates an entry for correctness. Returns an array of { field => array of errors }.
   * $original: the original, unmodified entry
   **/
  function validate($original) {
    $errors = [];

    if (!mb_strlen($this->description)) {
      $errors['description'][] = 'Descrierea nu poate fi vidă.';
    }

    if (($this->structStatus == Entry::STRUCT_STATUS_DONE) &&
        ($original->structStatus != Entry::STRUCT_STATUS_DONE) &&
        !util_isModerator(PRIV_EDIT)) {
      $errors['structStatus'][] = 'Doar moderatorii pot marca structurarea drept terminată. ' .
                                'Vă rugăm să folosiți valoarea „așteaptă moderarea”.';
    }

    if ($this->structuristId != $original->structuristId) {
      if (util_isModerator(PRIV_ADMIN)) {
        // Admins can modify this field
      } else if (($original->structuristId == session_getUserId()) &&
                 !$this->structuristId) {
        // Structurists can remove themselves
      } else if (!$original->structuristId &&
                 ($this->structuristId == session_getUserId()) &&
                 ($original->structStatus == Entry::STRUCT_STATUS_NEW) &&
                 ($this->structStatus == Entry::STRUCT_STATUS_IN_PROGRESS)) {
        // The system silently assigns structurists when they start the process
      } else if (!$original->structuristId &&
                 ($this->structuristId == session_getUserId()) &&
                 ($original->structStatus == Entry::STRUCT_STATUS_IN_PROGRESS) &&
                 ($this->structStatus == Entry::STRUCT_STATUS_IN_PROGRESS)) {
        // Structurists can claim orphan entries
      } else {
        $errors['structuristId'] = 'Nu puteți modifica structuristul, dar puteți (1) revendica ' .
                                 'o intrare în lucru fără structurist sau (2) renunța la ' .
                                 'o intrare dacă vi se pare prea greu de structurat.';
      }
    }

    return $errors;
  }

  public function mergeInto($otherId) {
    // Delete any empty trees of $this before the merge
    foreach ($this->getTrees() as $t) {
      $meaning = Meaning::get_by_treeId($t->id);
      if (!$meaning) {
        $t->delete();
      }
    }

    EntryDefinition::copy($this->id, $otherId, 1);
    EntryLexem::copy($this->id, $otherId, 1);
    TreeEntry::copy($this->id, $otherId, 2);

    $visuals = Visual::get_all_by_entryId($this->id);
    foreach ($visuals as $v) {
      $v->entryId = $otherId;
      $v->save();
    }

    $vts = VisualTag::get_all_by_entryId($this->id);
    foreach ($vts as $vt) {
      $vt->entryId = $otherId;
      $vt->save();
    }

    $this->delete();
  }

  public function delete() {
    EntryDefinition::delete_all_by_entryId($this->id);
    EntryLexem::delete_all_by_entryId($this->id);
    TreeEntry::delete_all_by_entryId($this->id);

    // orphan Visuals and VisualTags
    $visuals = Visual::get_all_by_entryId($this->id);
    foreach ($visuals as $v) {
      $v->entryId = 0;
      $v->save();
    }

    $vts = VisualTag::get_all_by_entryId($this->id);
    foreach ($vts as $vt) {
      $vt->entryId = 0;
      $vt->save();
    }

    Log::warning("Deleted entry {$this->id} ({$this->description})");
    parent::delete();
  }

}

?>
