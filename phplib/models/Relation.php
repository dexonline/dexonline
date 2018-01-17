<?php

class Relation extends BaseObject implements DatedObject {
  public static $_table = 'Relation';
  const TYPE_SYNONYM = 1;
  const TYPE_ANTONYM = 2;
  const TYPE_DIMINUTIVE = 3;
  const TYPE_AUGMENTATIVE = 4;
  const NUM_TYPES = 4;

  // for display purposes (the word "synonym" can sometimes be ommitted)
  const DEFAULT_TYPE = self::TYPE_SYNONYM;

  public static $TYPE_NAMES = [
    self::TYPE_SYNONYM  => 'sinonime',
    self::TYPE_ANTONYM  => 'antonime',
    self::TYPE_DIMINUTIVE  => 'diminutive',
    self::TYPE_AUGMENTATIVE  => 'augmentative',
  ];

  // Returns a meaning's related trees, mapped by type
  static function loadByMeaningId($meaningId) {
    $trees = Model::factory('Tree')
      ->select('Tree.*')
      ->select('Relation.type')
      ->join('Relation', ['Tree.id', '=', 'treeId'])
      ->where('Relation.meaningId', $meaningId)
      ->order_by_asc('description')
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
