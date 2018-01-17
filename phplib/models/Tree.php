<?php

class Tree extends BaseObject implements DatedObject {
  public static $_table = 'Tree';

  const ST_VISIBLE = 0;
  const ST_HIDDEN = 1;

  public static $STATUS_NAMES = [
    self::ST_VISIBLE  => 'vizibil',
    self::ST_HIDDEN  => 'ascuns',
  ];

  private $meanings = null;
  private $tags = null;

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
    return preg_split('/\s+[(\/]/', $this->description)[0];
  }

  function getTags() {
    if ($this->tags === null) {
      $this->tags = ObjectTag::getTags($this->id, ObjectTag::TYPE_TREE);
    }
    return $this->tags;
  }

  function hasMeanings() {
    return Model::factory('Meaning')
      ->where('treeId', $this->id)
      ->count();
  }

  function setMeanings($meanings) {
    $this->meanings = $meanings;
  }

  /* Returns a recursive tree of meanings */
  function getMeanings() {
    if ($this->meanings === null) {
      $meanings = Model::factory('Meaning')
                ->where('treeId', $this->id)
                ->order_by_asc('displayOrder')
                ->find_many();

      // Map the meanings by id
      $map = [];
      foreach ($meanings as $m) {
        $map[$m->id] = $m;
      }

      // Collect each node's children
      $children = [];
      foreach ($meanings as $m) {
        $children[$m->id] = [];
      }
      foreach ($meanings as $m) {
        if ($m->parentId) {
          $children[$m->parentId][$m->displayOrder] = $m->id;
        }
      }

      // Build a tree from every root
      $this->meanings = [];
      foreach ($meanings as $m) {
        if (!$m->parentId) {
          $this->meanings[] = $this->buildTree($map, $m->id, $children);
        }
      }
    }
    return $this->meanings;
  }

  /**
   * Returns a dictionary containing:
   * 'meaning': a Meaning object
   * 'sources', 'tags', 'relations': collections of objects related to the meaning
   * 'children': a recursive dictionary containing this meaning's children
   **/
  private function buildTree(&$map, $meaningId, &$children) {
    $mention = Mention::get_by_objectType_objectId(Mention::TYPE_MEANING, $meaningId);
    $meaning = $map[$meaningId];

    $results = [
      'meaning' => $meaning,
      'sources' => $meaning->getSources(),
      'tags' => Tag::loadByMeaningId($meaningId),
      'relations' => Relation::loadByMeaningId($meaningId),
      'children' => [],
      // Meaningful for etymologies: the breadcrumb of the lowest ancestor of TYPE_MEANING.
      // Populated by Tree::extractEtymologies().
      'lastBreadcrumb' => null,
      // meanings with incoming mentions cannot be deleted
      'canDelete' => !$mention,
    ];
    foreach ($children[$meaningId] as $childId) {
      $results['children'][] = self::buildTree($map, $childId, $children);
    }
    return $results;
  }

  function getEtymologies() {
    if ($this->etymologies === null) {
      $this->extractEtymologies();
    }
    return $this->etymologies;
  }

  function extractEtymologies() {
    $this->getMeanings();
    $this->etymologies = [];

    $this->extractEtymologiesHelper($this->meanings, null);
  }

  function extractEtymologiesHelper(&$meanings, $lastBreadcrumb) {
    if (!empty($meanings)) {
      foreach ($meanings as $i => &$t) {
        if ($t['meaning']->type == Meaning::TYPE_ETYMOLOGY) {
          $t['lastBreadcrumb'] = $lastBreadcrumb;
          $this->etymologies[] = $t;
          unset($meanings[$i]);
        } else {
          $bc = ($t['meaning']->breadcrumb) ? $t['meaning']->breadcrumb : $lastBreadcrumb;
          $this->extractEtymologiesHelper($t['children'], $bc);
        }
      }
    }
  }

  /* When displaying search results, examples are special, so we separate them from the
   * other child meanings. */
  function extractExamples() {
    $this->getMeanings();
    $this->extractExamplesHelper($this->meanings);
  }

  function extractExamplesHelper(&$meanings) {
    foreach ($meanings as &$t) {
      $this->extractExamplesHelper($t['children']);
      $t['examples'] = [];
      foreach ($t['children'] as $i => $child) {
        if ($child['meaning']->type == Meaning::TYPE_EXAMPLE) {
          $t['examples'][] = $child;
          unset($t['children'][$i]);
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
   * Collects the lexemes of all entries associated with this tree.
   * Returns the list of lexemes sorted with main lexemes first.
   * Excludes duplicate lexemes and lexemes that have a form equal to the tree's description.
   **/
  function getPrintableLexems() {
    return Model::factory('Lexem')
      ->table_alias('l')
      ->select('l.*')
      ->select('el.main')
      ->distinct()
      ->join('EntryLexem', ['l.id', '=', 'el.lexemId'], 'el')
      ->join('TreeEntry', ['el.entryId', '=', 'te.entryId'], 'te')
      ->where('te.treeId', $this->id)
      ->where_not_equal('l.formNoAccent', $this->getShortDescription())
      ->order_by_desc('el.main')
      ->order_by_asc('l.formNoAccent')
      ->find_many();
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

    $relations = Relation::get_all_by_treeId($this->id);
    foreach ($relations as $r) {
      $r->treeId = $otherId;
      $r->save();
    }

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
    $newt->description .= ' (CLONĂ)';
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

  function canDelete() {
    $numMeanings = Model::factory('Meaning')
                 ->where('treeId', $this->id)
                 ->count();
    $numRelations = Model::factory('Relation')
                  ->where('treeId', $this->id)
                  ->count();
    $numMeaningMentions = Model::factory('Mention')
                        ->table_alias('m')
                        ->join('Meaning', ['m.objectId', '=', 'mg.id'], 'mg')
                        ->where('m.objectType', Mention::TYPE_MEANING)
                        ->where('mg.treeId', $this->id)
                        ->count();
    $numTreeMentions = Model::factory('Mention')
                     ->where('objectType', Mention::TYPE_TREE)
                     ->where('objectId', $this->id)
                     ->count();
    return !$numMeanings && !$numRelations && !$numMeaningMentions && !$numTreeMentions;
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
      $m->htmlRep = Str::htmlize($m->internalRep, 0);
      $m->save();
    }
    Mention::delete_all_by_objectId_objectType($this->id, Mention::TYPE_TREE);

    Log::warning("Deleted tree {$this->id} ({$this->description})");
    parent::delete();
  }

}
