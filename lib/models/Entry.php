<?php

class Entry extends BaseObject implements DatedObject {
  public static $_table = 'Entry';

  const STRUCT_STATUS_NEW = 1;
  const STRUCT_STATUS_IN_PROGRESS = 2;
  const STRUCT_STATUS_UNDER_REVIEW = 3;
  const STRUCT_STATUS_DONE = 4;

  const STRUCTURIST_ID_ANY = -1;
  const STRUCTURIST_ID_NONE = 0;

  const STRUCT_STATUS_NAMES = [
    self::STRUCT_STATUS_NEW => 'neîncepută',
    self::STRUCT_STATUS_IN_PROGRESS => 'în lucru',
    self::STRUCT_STATUS_UNDER_REVIEW => 'așteaptă moderarea',
    self::STRUCT_STATUS_DONE => 'terminată',
  ];

  // create and associate and empty tree if $tree == true
  static function createAndSave($description, $tree = false) {
    $e = Model::factory('Entry')->create();
    $e->description = $description;
    $e->structStatus = self::STRUCT_STATUS_NEW;
    $e->save();

    if ($tree) {
      $t = Tree::createAndSave($description);
      TreeEntry::associate($t->id, $e->id);
    }

    return $e;
  }

  function _clone($cloneDefinitions, $cloneLexemes, $cloneTrees, $cloneStructurist) {
    $e = $this->parisClone();

    if (!$cloneStructurist) {
      $e->structStatus = self::STRUCT_STATUS_NEW;
      $e->structuristId = 0;
    }
    $e->save();

    if ($cloneDefinitions) {
      EntryDefinition::copy($this->id, $e->id, 1);
    }

    if ($cloneLexemes) {
      EntryLexeme::copy($this->id, $e->id, 1, ['main' => true]);
      EntryLexeme::copy($this->id, $e->id, 1, ['main' => false]);
    }

    if ($cloneTrees) {
      TreeEntry::copy($this->id, $e->id, 2);
    }

    return $e;
  }

  function save() {
    $this->modUserId = User::getActiveId();
    parent::save();
  }

  function loadMeanings() {
    foreach ($this->getTrees() as $t) {
      $t->getMeanings();
    }
  }

  // Returns the description up to the first parenthesis (if any).
  function getShortDescription() {
    return preg_split('/\s+[(\/]/', $this->description)[0];
  }

  function getTags() {
    return Preload::getEntryTags($this->id);
  }

  /**
   * Returns the list of lexemes sorted with main lexemes first. Excludes
   * lexemes that have a form equal to the entry's description. Lexemes with
   * identical values of formNoAccent are only collected once.
   *
   * Due to the mechanics of getMainLexemes() / getVariants(), lexemes will be
   * sorted by the main bit, then by lexemeRank.
   **/
  function getPrintableLexemes() {
    $desc = $this->getShortDescription();
    $seen = [ $desc => true ];
    $results = [];

    foreach ([true, false] as $main) {
      $lexemes = $main ? $this->getMainLexemes() : $this->getVariants();

      foreach ($lexemes as $l) {
        $form = $l->formNoAccent;
        if (!isset($seen[$form])) {
          $seen[$form] = true;
          $l->main = $main;
          $results[] = $l;
        }
      }
    }

    return $results;
  }

  static function loadUnassociated() {
    $query = 'select * from Entry ' .
           'where id not in (select entryId from EntryLexeme) ' .
           'or id not in (select entryId from EntryDefinition)';
    return Model::factory('Entry')
      ->raw_query($query)
      ->find_many();
  }

  /**
   * For every set of entries having the same case-sensitive description, load one of them at random.
   */
  static function loadAmbiguous() {
    // The key here is to create a subquery of all the case-insensitiv descriptions
    // appearing at least twice.
    $query = 'select * from Entry ' .
           'join (select description d from Entry group by description having count(*) > 1) dup ' .
           'on description = d ' .
           'group by binary description ' .
           'having count(*) > 1 ' .
           'order by description';
    return Model::factory('Entry')->raw_query($query)->find_many();
  }

  /**
   * Load entries marked as under review or structured having definitions that still need to
   * be structured.
   **/
  static function loadWithDefinitionsToStructure() {
    return Model::factory('Entry')
      ->table_alias('e')
      ->select('e.*')
      ->distinct()
      ->join('EntryDefinition', ['e.id', '=', 'ed.entryId'], 'ed')
      ->join('Definition', ['ed.definitionId', '=', 'd.id'], 'd')
      ->join('Source', ['d.sourceId', '=', 's.id'], 's')
      ->where_in('e.structStatus', [self::STRUCT_STATUS_UNDER_REVIEW, self::STRUCT_STATUS_DONE])
      ->where('d.structured', 0)
      ->where_in('d.status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
      ->where('s.structurable', 1)
      ->order_by_asc('e.description')
      ->find_many();
  }

  /**
   * Load entries that have variant lexemes, but no main lexemes.
   **/
  static function loadWithoutMainLexemes() {
    $ids = Model::factory('EntryLexeme')
      ->select('entryId')
      ->group_by('entryId')
      ->having_raw('max(main) = 0')
      ->find_array();
    $ids = array_column($ids, 'entryId');

    if (empty($ids)) {
      return [];
    } else {
      return Model::factory('Entry')
        ->where_in('id', $ids)
        ->order_by_asc('description')
        ->find_many();
    }
  }

    /**
   * Returns, with constraints, entries that have multiple main lexemes
   *
   * @param   array   $structStatus
   * @param   bool    $onlyCount        abbreviation short form
   * @param   int     $limit            do to go over 5000
   * @return  ORMWrapper
   */
  static function loadWithMultipleMainLexemes($onlyCount = true, $limit = 100) {

    $query = Model::factory('Entry')
      ->table_alias('e')
      ->select('e.*')
      ->select_expr('sum(el.main)', 'mainCount')
      ->join('EntryLexeme', ['e.id', '=', 'el.entryId'], 'el')
      ->where('e.structStatus', self::STRUCT_STATUS_DONE)
      ->where('el.main', 1)
      ->group_by('e.id')
      ->having_raw('(!e.multipleMains and mainCount != 1) or (e.multipleMains and mainCount <= 1)');

    if ($onlyCount) {
      $query = $query->find_result_set()->count();
    }
    else {
      $query = $query
        ->join('Lexeme', ['l.id', '=', 'el.lexemeId'], 'l')
        ->join('User', ['u.id', '=', 'e.modUserId'], 'u')
        ->select('u.nick', 'nick')
        ->order_by_asc('description')
        ->limit($limit)
        ->find_many();
    }
    return $query;
  }

  static function searchInflectedForms($cuv, $hasDiacritics) {
    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';

    // load lexemes from two sources:
    // * simple lexemes that generate this form;
    // * compound lexemes that have a fragment that generates this form
    //   (do not include compound lexemes for prepositions like "de" or "a")

    $simple = 'select l.id ' .
      'from Lexeme l ' .
      'join InflectedForm i on l.id = i.lexemeId ' .
      "where i.$field = :form";
    $compound = 'select f.lexemeId ' .
      'from Fragment f ' .
      'join Lexeme part on f.partId = part.id ' .
      'join InflectedForm i on f.partId = i.lexemeId ' .
      "where i.$field = :form " .
      'and (part.modelType != "I" or part.modelNumber not in ("11", "12"))';
    $subquery = "{$simple} union {$compound}";

    // load entries for the above lexemes
    $query = 'select distinct e.* ' .
      'from Entry e ' .
      'join EntryLexeme el on e.id = el.entryId ' .
      "join ({$subquery}) l on el.lexemeId = l.id " .
      'order by (e.description != :form), ' . // exact match
      '(e.description not like concat (:form, " (%")), ' . // partial match
      'e.description';

    $entries = Model::factory('Entry')
             ->raw_query($query, ['form' => $cuv])
             ->find_many();

    return $entries;
  }

  function getTrees() {
    return Preload::getEntryTrees($this->id);
  }

  // Returns the first main lexeme (or the first lexeme if none of them are main).
  function getMainLexeme() {
    return Model::factory('Lexeme')
      ->table_alias('l')
      ->select('l.*')
      ->join('EntryLexeme', ['l.id', '=', 'el.lexemeId'], 'el')
      ->where('el.entryId', $this->id)
      ->where('el.main', true)
      ->order_by_asc('el.lexemeRank')
      ->find_one();
  }

  function getLexemes() {
    return Preload::getEntryLexemes($this->id);
  }

  function getMainLexemeIds() {
    return $this->getLexemeIds(['main' => true]);
  }

  function getVariantLexemeIds() {
    return $this->getLexemeIds(['main' => false]);
  }

  function getMainLexemes() {
    return Preload::getEntryMainLexemes($this->id);
  }

  function getVariants() {
    return Preload::getEntryVariants($this->id);
  }

  function hasVariants() {
    return (!empty($this->getVariants()));
  }

  static function getHomonyms($entries) {
    $entryIds = [];
    $homonymIds = [];

    foreach ($entries as $e) {
      $entryIds[] = $e->id;

      foreach ($e->getLexemes() as $l) {
        $homonymEntries = Model::factory('EntryLexeme')
                        ->table_alias('el')
                        ->select('el.entryId')
                        ->join('Lexeme', ['el.lexemeId', '=', 'l.id'], 'l')
                        ->where('l.formNoAccent', $l->formNoAccent)
                        ->find_array();
        foreach ($homonymEntries as $h) {
          $homonymIds[$h['entryId']] = true;
        }
      }
    }

    if (empty($homonymIds)) {
      $homonyms = [];
    } else {
      $homonyms = Model::factory('Entry')
                ->where_in('id', array_keys($homonymIds))
                ->where_not_in('id', $entryIds)
                ->find_many();
    }

    return $homonyms;
  }

  /**
   * Validates an entry for correctness. Returns an array of { field => array of errors }.
   * @param Entry $original the original, unmodified entry
   * @param array $mainLexemeIds main lexemes
   **/
  function validate($original, $mainLexemeIds) {
    $errors = [];

    if (!mb_strlen($this->description)) {
      $errors['description'][] = 'Descrierea nu poate fi vidă.';
    }

    if (($this->structStatus == Entry::STRUCT_STATUS_DONE) &&
        ($original->structStatus != Entry::STRUCT_STATUS_DONE) &&
        !User::can(User::PRIV_EDIT)) {
      $errors['structStatus'][] = 'Doar moderatorii pot marca structurarea drept terminată. ' .
                                'Vă rugăm să folosiți valoarea „așteaptă moderarea”.';
    }

    if ($this->structuristId != $original->structuristId) {
      if (User::can(User::PRIV_ADMIN)) {
        // Admins can modify this field
      } else if (($original->structuristId == User::getActiveId()) &&
                 !$this->structuristId) {
        // Structurists can remove themselves
      } else if (!$original->structuristId &&
                 ($this->structuristId == User::getActiveId()) &&
                 ($original->structStatus == Entry::STRUCT_STATUS_NEW) &&
                 ($this->structStatus == Entry::STRUCT_STATUS_IN_PROGRESS)) {
        // The system silently assigns structurists when they start the process
      } else if (!$original->structuristId &&
                 ($this->structuristId == User::getActiveId()) &&
                 ($original->structStatus == Entry::STRUCT_STATUS_IN_PROGRESS) &&
                 ($this->structStatus == Entry::STRUCT_STATUS_IN_PROGRESS)) {
        // Structurists can claim orphan entries
      } else {
        $errors['structuristId'] = 'Nu puteți modifica structuristul, dar puteți (1) revendica ' .
                                 'o intrare în lucru fără structurist sau (2) renunța la ' .
                                 'o intrare dacă vi se pare prea greu de structurat.';
      }
    }

    $mains = count($mainLexemeIds);
    if (!$mains) {
      $errors['mainLexemeIds'] = 'Nu ați ales un lexem principal.';
    }
    if ((($mains == 1) && $this->multipleMains) ||
        (($mains > 1) && !$this->multipleMains)) {
      $errors['multipleMains'] = 'Bifa nu reflectă numărul de lexeme principale alese.';
    }

    return $errors;
  }

  // delete associated lexemes if
  // 1. they are temporary (model type T);
  // 2. they are only associated with this entry, no other entries;
  // 3. the entry has another, non-T lexeme with the same base form.
  function deleteTemporaryLexemes() {

    // collect forms of non-T lexemes
    $formMap = [];
    foreach ($this->getLexemes() as $l) {
      if ($l->modelType != 'T') {
        $formMap[$l->formNoAccent] = true;
      }
    }

    foreach ($this->getLexemes() as $l) {
      if (($l->modelType == 'T') &&
          isset($formMap[$l->formNoAccent]) &&
          (count($l->getEntries()) == 1)) {
        $l->delete();
      }
    }
  }

  function mergeInto($other) {
    // delete trees that are safe to delete
    foreach ($this->getTrees() as $t) {
      if ($t->canDelete()) {
        $t->delete();
      }
    }

    EntryDefinition::copy($this->id, $other->id, 1);
    EntryLexeme::copy($this->id, $other->id, 1, ['main' => true]);
    EntryLexeme::copy($this->id, $other->id, 1, ['main' => false]);
    TreeEntry::copy($this->id, $other->id, 2);

    $visuals = Visual::get_all_by_entryId($this->id);
    foreach ($visuals as $v) {
      $v->entryId = $other->id;
      $v->save();
    }

    $vts = VisualTag::get_all_by_entryId($this->id);
    foreach ($vts as $vt) {
      $vt->entryId = $other->id;
      $vt->save();
    }

    $this->delete();

    // recompute $other->multipleMains
    $numMains = EntryLexeme::count_by_entryId_main($other->id, true);
    $other->multipleMains = ($numMains > 1);

    $other->save(); // also forces updating the modUserId field
    $other->deleteTemporaryLexemes();
  }

  function delete() {
    EntryDefinition::delete_all_by_entryId($this->id);
    EntryLexeme::delete_all_by_entryId($this->id);
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

  function __toString() {
    return $this->description;
  }

}
