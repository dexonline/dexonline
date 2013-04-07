<?php

class Meaning extends BaseObject implements DatedObject {
  public static $_table = 'Meaning';

  static function loadTree($lexemId) {
    $meanings = Model::factory('Meaning')->where('lexemId', $lexemId)->order_by_asc('displayOrder')->find_many();

    // Map the meanings by id
    $map = array();
    foreach ($meanings as $m) {
      $map[$m->id] = $m;
    }

    // Collect each node's children
    $children = array();
    foreach ($meanings as $m) {
      $children[$m->id] = array();
    }
    foreach ($meanings as $m) {
      if ($m->parentId) {
        $children[$m->parentId][$m->displayOrder] = $m->id;
      }
    }

    // Build a tree from every root
    $results = array();
    foreach ($meanings as $m) {
      if (!$m->parentId) {
        $results[] = self::buildTree($map, $m->id, $children);
      }
    }
    return $results;
  }

  /** Returns a dictionary containing:
   * 'meaning': a Meaning object
   * 'children': a recusrive dictionary containing this meaning's children
   **/
  private static function buildTree(&$map, $meaningId, &$children) {
    $results = array('meaning' => $map[$meaningId],
                     'sources' => MeaningSource::loadSourcesByMeaningId($meaningId),
                     'children' => array());
    foreach ($children[$meaningId] as $childId) {
      $results['children'][] = self::buildTree($map, $childId, $children);
    }
    return $results;
  }
}

?>
