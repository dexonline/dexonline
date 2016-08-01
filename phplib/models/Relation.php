<?php

class Relation extends BaseObject implements DatedObject {
  public static $_table = 'Relation';
  const TYPE_SYNONYM = 1;
  const TYPE_ANTONYM = 2;
  const TYPE_DIMINUTIVE = 3;
  const TYPE_AUGMENTATIVE = 4;
  const NUM_TYPES = 4;

  // Returns a meaning's related trees, mapped by type
  static function loadByMeaningId($meaningId) {
    $trees = Model::factory('Tree')
      ->select('Tree.*')
      ->select('Relation.type')
      ->join('Relation', ['Tree.id', '=', 'treeId'])
      ->where('Relation.meaningId', $meaningId)
      ->order_by_asc('formNoAccent')
      ->find_many();
    $results = [];
    for ($i = 1; $i <= self::NUM_TYPES; $i++) {
      $results[$i] = [];
    }
    foreach ($trees as $t) {
      $results[$t->type][] = $t;
    }
    return $results;
  }

  // Returns a meaning's related trees, given a map of relation type to tree ID array
  static function loadRelatedTrees($map) {
    $results = [];
    foreach ($map as $type => $treeIds) {
      if ($type) {
        $results[$type] = Tree::loadByIds($treeIds);
      }
    }
    return $results;
  }
}

?>
