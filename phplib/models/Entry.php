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

  function _clone($cloneDefinitions, $cloneTrees) {
    $e = $this->parisClone();
    $e->description .= ' (CLONĂ)';
    $e->save();

    if ($cloneDefinitions) {
      $eds = EntryDefinition::get_all_by_entryId($this->id);
      foreach ($eds as $ed) {
        EntryDefinition::associate($e->id, $ed->definitionId);
      }
    }

    if ($cloneTrees) {
      $tes = TreeEntry::get_all_by_entryId($this->id);
      foreach ($tes as $te) {
        TreeEntry::associate($te->treeId, $e->id);
      }
    }

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
    $eds = EntryDefinition::get_all_by_entryId($this->id);
    foreach ($eds as $ed) {
      EntryDefinition::associate($otherId, $ed->definitionId);
    }

    $tes = TreeEntry::get_all_by_entryId($this->id);
    foreach ($tes as $te) {
      TreeEntry::associate($te->treeId, $otherId);
    }

    $lexems = Lexem::get_all_by_entryId($this->id);
    foreach ($lexems as $l) {
      $l->entryId = $otherId;
      $l->save();
    }

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
    EntryDefinition::deleteByEntryId($this->id);
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
