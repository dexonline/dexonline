<?php

class Tree extends BaseObject implements DatedObject {
  public static $_table = 'Tree';

  const ST_VISIBLE = 0;
  const ST_HIDDEN = 1;

  const STATUS_NAMES = [
    self::ST_VISIBLE  => 'vizibil',
    self::ST_HIDDEN  => 'ascuns',
  ];

  // passed by the tree editor in case there are errors
  private $meaningMap = null;

  // an array of etymologies extracted from $meanings[$i]
  private $etymologies = null;

  static function createAndSave($description) {
    $t = Model::factory('Tree')->create();
    $t->description = $description;
    $t->save();
    return $t;
  }

  // Returns the description up to the first parenthesis (if any).
  function getShortDescription() {
    $desc = preg_split('/,/', $this->description)[0];
    return preg_split('/\s+[(\/]/', $desc)[0];
  }

  function getEntries() {
    return Preload::getTreeEntries($this->id);
  }

  function getTags() {
    return Preload::getTreeTags($this->id);
  }

  /**
   * Returns main lexemes from structured entries.
   */
  function getPrintableLexemes() {
    $lexemes = Preload::getTreeLexemes($this->id);
    $lexemes = Lexeme::filterVerbs($lexemes);

    // compile lexemes and sets of pos tags
    $tuples = [];
    foreach ($lexemes as $l) {
      $tags = [];
      foreach ($l->getTags() as $t) {
        if ($t->isPos) {
          $tags[] = $t;
        }
      }
      $tuples[] = [
        'lexeme' => $l,
        'tags' => $tags,
      ];
    }

    // merge lexemes having identical tags
    $results = [];
    foreach ($tuples as $tuple) {
      $match = false;
      foreach ($results as $i => $res) {
        // compare $res.tags with $tuple.tags
        if (count($res['tags']) == count($tuple['tags'])) {
          $sameTags = true;
          foreach ($res['tags'] as $j => $tag) {
            if ($tag->id != $tuple['tags'][$j]->id) {
              $sameTags = false;
            }
          }
          if ($sameTags) {
            $match = $i;
          }
        }
      }

      if ($match !== false) {
        $results[$match]['lexemes'][] = $tuple['lexeme'];
      } else {
        $results[] = [
          'lexemes' => [ $tuple['lexeme'] ],
          'tags' => $tuple['tags'],
        ];
      }
    }

    return $results;
  }

  function hasMeanings() {
    return Model::factory('Meaning')
      ->where('treeId', $this->id)
      ->count();
  }

  function setMeanings($meanings) {
    $this->meaningMap = $meanings;
  }

  // prefer meanings previously set by setMeanings()
  function &getMeanings() {
    if ($this->meaningMap !== null) {
      return $this->meaningMap;
    } else {
      return Preload::getTreeMeanings($this->id);
    }
  }

  function getEtymologies() {
    if ($this->etymologies === null) {
      $this->extractEtymologies();
    }
    return $this->etymologies;
  }

  function extractEtymologies() {
    $meanings = &$this->getMeanings();
    $this->etymologies = [];
    $this->extractEtymologiesHelper($meanings, null);
  }

  function extractEtymologiesHelper(&$meanings, $lastBreadcrumb) {
    if (!empty($meanings)) {
      foreach ($meanings as $i => &$t) {
        if ($t['meaning']->type == Meaning::TYPE_ETYMOLOGY) {
          $t['lastBreadcrumb'] = $lastBreadcrumb;
          $this->etymologies[] = $t;
          unset($meanings[$i]);
        } else {
          $bc = $t['meaning']->breadcrumb ?: $lastBreadcrumb;
          $this->extractEtymologiesHelper($t['children'], $bc);
        }
      }
    }
  }

  /* When displaying search results, examples and expressions are special, so
   * we separate them from the other child meanings. */
  function extractDetails() {
    $meanings = &$this->getMeanings();
    $this->extractDetailsHelper($meanings);
  }

  function extractDetailsHelper(&$meanings) {
    foreach ($meanings as &$t) {
      $this->extractDetailsHelper($t['children']);
      $t['examples'] = [];
      $t['expressions'] = [];
      foreach ($t['children'] as $i => $child) {
        switch ($child['meaning']->type) {
          case Meaning::TYPE_EXAMPLE:
            $t['examples'][] = $child;
            unset($t['children'][$i]);
            break;
          case Meaning::TYPE_EXPRESSION:
            $t['expressions'][] = $child;
            unset($t['children'][$i]);
            break;
        }
      }
    }
  }

  /* Return meanings that are in relation with this tree. */
  function getRelatedMeanings() {
    return Model::factory('Meaning')
      ->table_alias('m')
      ->select('m.*')
      ->select('r.type', 'relationType')
      ->join('Relation', ['m.id', '=', 'r.meaningId'], 'r')
      ->where('r.treeId', $this->id)
      ->find_many();
  }

  function getHomonyms() {
    // get the part before the parenthesis (if any)
    $parts = explode('(', $this->description);
    $desc = trim($parts[0]);

    return Model::factory('Tree')
      ->where_any_is([['description' => $desc],
                      ['description' => "{$desc} (%"]],
                     'like')
      ->where_not_equal('id', $this->id)
      ->find_many();
  }

  function getTreesFromSameEntries() {
    return Model::factory('Tree')
      ->table_alias('t')
      ->select('t.*')
      ->distinct()
      ->join('TreeEntry', ['te1.treeId', '=', 't.id'], 'te1')
      ->join('TreeEntry', ['te2.entryId', '=', 'te1.entryId'], 'te2')
      ->where('te2.treeId', $this->id)
      ->where_not_equal('t.id', $this->id)
      ->find_many();
  }

  // returns homonyms and trees from the same entries, but removes duplicates
  function getRelatedTrees() {
    $result = $this->getHomonyms();
    $ids = Util::objectProperty($result, 'id');

    foreach ($this->getTreesFromSameEntries() as $t) {
      if (!in_array($t->id, $ids)) {
        $result[] = $t;
      }
    }

    return $result;
  }

  /**
   * Counts trees not associated with any entries.
   **/
  static function countUnassociated() {
    $numTrees = Model::factory('Tree')->count();
    $numAssociated = DB::getSingleValue('select count(distinct treeId) from TreeEntry');
    return $numTrees - $numAssociated;
  }

  /**
   * Validates a tree for correctness. Returns an array of { field => array of errors }.
   **/
  function validate() {
    $errors = [];

    if (!mb_strlen($this->description)) {
      $errors['description'][] = 'Descrierea nu poate fi vidă.';
    }

    if ($this->status == self::ST_HIDDEN) {
      // Look for meanings from visible trees that are in a relation with us.
      $count = Model::factory('Meaning')
             ->table_alias('m')
             ->join('Tree', ['m.treeId', '=', 't.id'], 't') // not us, but the meaning's tree
             ->join('Relation', ['m.id', '=', 'r.meaningId'], 'r')
             ->where('t.status', self::ST_VISIBLE)
             ->where('r.treeId', $this->id)
             ->count();
      if ($count) {
        $errors['status'][] = 'Nu puteți ascunde arborele, deoarece alte sensuri sunt în relație cu el.';
      }
    }

    return $errors;
  }

  /**
   * Transfers relations from this tree to another tree. Avoids creating
   * duplicates with the same meaningId and type.
   */
  private function transferRelations($otherId) {
    if (!$otherId) {
      return;
    }

    // map other tree's relations
    $relMap = [];
    $otherRelations = Relation::get_all_by_treeId($otherId);
    foreach ($otherRelations as $r) {
      $relMap[$r->meaningId][$r->type] = true;
    }

    // transfer this tree's relations
    $relations = Relation::get_all_by_treeId($this->id);
    foreach ($relations as $r) {
      if (isset($relMap[$r->meaningId][$r->type])) {
        $r->delete();
      } else {
        $r->treeId = $otherId;
        $r->save();
      }
    }
  }

  function mergeInto($otherId) {
    // Meanings will be renumbered, increasing their displayOrder and breadcrumb values
    $deltaDisplayOrder = Model::factory('Meaning')
                       ->where('treeId', $otherId)
                       ->count();
    $deltaBreadcrumb = Model::factory('Meaning')
                     ->where('treeId', $otherId)
                     ->where('parentId', 0)
                     ->count();

    TreeEntry::copy($this->id, $otherId, 1);

    $this->transferRelations($otherId);

    $meanings = Meaning::get_all_by_treeId($this->id);
    foreach ($meanings as $m) {
      $m->displayOrder += $deltaDisplayOrder;
      $m->increaseBreadcrumb($deltaBreadcrumb);
      $m->treeId = $otherId;
      $m->save();
    }

    $this->delete();
  }

  function _clone() {
    $newt = $this->parisClone();
    $newt->save();

    TreeEntry::copy($this->id, $newt->id, 1);

    // Clone meanings in displayOrder. This guarantees that a parent is cloned before its children.
    $meanings = Model::factory('Meaning')
              ->where('treeId', $this->id)
              ->order_by_asc('displayOrder')
              ->find_many();
    $meaningIdMap = []; // map original meaning IDs to clone IDs

    foreach ($meanings as $m) {
      $newm = $m->parisClone();
      $newm->treeId = $newt->id;
      if ($newm->parentId) {
        $newm->parentId = $meaningIdMap[$newm->parentId];
      }
      $newm->save();
      $meaningIdMap[$m->id] = $newm->id;

      // clone the meaning's sources, relations and tags
      MeaningSource::copy($m->id, $newm->id, 1);

      $rels = Relation::get_all_by_meaningId($m->id);
      foreach ($rels as $r) {
        $newr = $r->parisClone();
        $newr->meaningId = $newm->id;
        $newr->save();
      }

      $ots = ObjectTag::getMeaningTags($m->id);
      foreach ($ots as $ot) {
        $new = $ot->parisClone();
        $new->objectId = $newm->id;
        $new->save();
      }
    }
    return $newt;
  }

  function save() {
    $this->descriptionSort = $this->description;
    parent::save();
  }

  function canDelete() {
    $numMeanings = Model::factory('Meaning')
                 ->where('treeId', $this->id)
                 ->count();
    $numRelations = Model::factory('Relation')
                  ->where('treeId', $this->id)
                  ->count();
    $numTreeMentions = Model::factory('Mention')
                     ->where('objectType', Mention::TYPE_TREE)
                     ->where('objectId', $this->id)
                     ->count();
    // no need to also check for incoming meaning mentions, because there are no meanings
    return !$numMeanings && !$numRelations && !$numTreeMentions;
  }

  /**
   * This should only be called on trees with no meanings and no relations.
   **/
  function delete() {
    Meaning::delete_all_by_treeId($this->id);
    Relation::delete_all_by_treeId($this->id);
    TreeEntry::delete_all_by_treeId($this->id);

    // Reprocess meanings mentioning this tree to remove said mentions
    $mentions = Mention::getTreeMentions($this->id);
    foreach ($mentions as $ment) {
      $m = Meaning::get_by_id($ment->meaningId);
      $m->internalRep = str_replace("[[{$this->id}]]", '', $m->internalRep);
      $m->process();
      $m->save();
    }
    Mention::delete_all_by_objectId_objectType($this->id, Mention::TYPE_TREE);

    Log::warning("Deleted tree {$this->id} ({$this->description})");
    parent::delete();
  }

}
