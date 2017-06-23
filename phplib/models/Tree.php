<?php

class Tree extends BaseObject implements DatedObject {
  public static $_table = 'Tree';

  const ST_VISIBLE = 0;
  const ST_HIDDEN = 1;

  public static $STATUS_NAMES = [
    self::ST_VISIBLE  => 'vizibil',
    self::ST_HIDDEN  => 'ascuns',
  ];

  private $entries = null;
  private $meanings = null;

  // an array of etymologies extracted from $meanings[$i]
  private $etymologies = null;

  static function createAndSave($description) {
    $t = Model::factory('Tree')->create();
    $t->description = $description;
    $t->save();
    return $t;
  }

  function getEntries() {
    if ($this->entries === null) {
      $this->entries = Model::factory('Entry')
                     ->table_alias('e')
                     ->select('e.*')
                     ->join('TreeEntry', ['te.entryId', '=', 'e.id'], 'te')
                     ->where('te.treeId', $this->id)
                     ->order_by_asc('te.id')
                     ->find_many();
    }
    return $this->entries;
  }

  function getEntryIds() {
    $result = [];
    foreach ($this->getEntries() as $e) {
      $result[] = $e->id;
    }
    return $result;
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
    $results = [
      'meaning' => $map[$meaningId],
      'sources' => MeaningSource::loadSourcesByMeaningId($meaningId),
      'tags' => Tag::loadByMeaningId($meaningId),
      'relations' => Relation::loadByMeaningId($meaningId),
      'children' => [],
      // Meaningful for etymologies: the breadcrumb of the lowest ancestor of TYPE_MEANING.
      // Populated by Tree::extractEtymologies().
      'lastBreadcrumb' => null,
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

    $this->cloneMeanings($this->getMeanings(), 0, $newt->id);

    return $newt;
  }

  function cloneMeanings($meanings, $parentId, $newTreeId) {
    foreach ($meanings as $rec) {
      $m = $rec['meaning'];

      // update the treeId and parentId fields
      $newm = $m->parisClone();
      $newm->parentId = $parentId;
      $newm->treeId = $newTreeId;
      $newm->save();

      // copy the meaning sources, meaning tags and relations
      foreach (['MeaningSource', 'Relation'] as $className) {
        $oldSet = $className::get_all_by_meaningId($m->id);
        foreach ($oldSet as $old) {
          $new = $old->parisClone();
          $new->meaningId = $newm->id;
          $new->save();
        }
      }

      $ots = ObjectTag::getMeaningTags($m->id);
      foreach ($ots as $ot) {
        $new = $ot->parisClone();
        $new->objectId = $newm->id;
        $new->save();
      }

      $this->cloneMeanings($rec['children'], $newm->id, $newTreeId);
    }
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
      $m->htmlRep = AdminStringUtil::htmlize($m->internalRep, 0);
      $m->save();
    }
    Mention::delete_all_by_objectId_objectType($this->id, Mention::TYPE_TREE);

    Log::warning("Deleted tree {$this->id} ({$this->description})");
    parent::delete();
  }

}

?>
