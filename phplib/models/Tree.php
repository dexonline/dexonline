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
    $results = array('meaning' => $map[$meaningId],
                     'sources' => MeaningSource::loadSourcesByMeaningId($meaningId),
                     'tags' => Tag::loadByMeaningId($meaningId),
                     'relations' => Relation::loadByMeaningId($meaningId),
                     'children' => []);
    foreach ($children[$meaningId] as $childId) {
      $results['children'][] = self::buildTree($map, $childId, $children);
    }
    return $results;
  }

  /**
   * Validates a tree for correctness. Returns an array of { field => array of errors }.
   **/
  function validate() {
    $errors = [];

    if (!mb_strlen($this->description)) {
      $errors['description'][] = _('Descrierea nu poate fi vidă.');
    }

    return $errors;
  }

  function clone() {
    $newt = $this->parisClone();
    $newt->description .= ' (CLONĂ)';
    $newt->save();

    $tes = TreeEntry::get_all_by_treeId($this->id);
    foreach ($tes as $te) {
      TreeEntry::associate($newt->id, $te->entryId);
    }

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
      foreach (['MeaningSource', 'MeaningTag', 'Relation'] as $className) {
        $oldSet = $className::get_all_by_meaningId($m->id);
        foreach ($oldSet as $old) {
          $new = $old->parisClone();
          $new->meaningId = $newm->id;
          $new->save();
        }
      }

      $this->cloneMeanings($rec['children'], $newm->id, $newTreeId);
    }
  }

  public function delete() {
    TreeEntry::delete_all_by_treeId($this->id);

    $meanings = Meaning::get_all_by_treeId($this->id);
    foreach ($meanings as $m) {
      $m->delete();
    }

    Log::warning("Deleted tree {$this->id} ({$this->description})");
    parent::delete();
  }

}

?>
