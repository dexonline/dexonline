<?php

class Tree extends BaseObject implements DatedObject {
  public static $_table = 'Tree';

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

  public function delete() {
    TreeEntry::delete_all_by_treeId($this->id);
    Log::warning("Deleted tree {$this->id} ({$this->description})");
    parent::delete();
  }

}

?>
