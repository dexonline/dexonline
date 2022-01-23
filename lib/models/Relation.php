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

  const TYPE_NAMES = [
    self::TYPE_SYNONYM  => 'sinonime',
    self::TYPE_ANTONYM  => 'antonime',
    self::TYPE_DIMINUTIVE  => 'diminutive',
    self::TYPE_AUGMENTATIVE  => 'augmentative',
  ];

  static function getTypeName($type) {
    return self::TYPE_NAMES[$type];
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
