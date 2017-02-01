<?php

/**
 * A Mention is a reference from within a Meaning.internalRep, either to
 * another Meaning or to a Tree.
 **/

class Mention extends BaseObject implements DatedObject {
  public static $_table = 'Mention';

  const TYPE_MEANING = 1;
  const TYPE_TREE = 2;

  static function getAllByIdType($objectId, $objectType) {
    return Model::factory('Mention')
      ->where('objectId', $objectId)
      ->where('objectType', $objectType)
      ->order_by_asc('id')
      ->find_many();
  }

  // Returns mentions having this meaning as *destination*.
  static function getMeaningMentions($meaningId) {
    return self::getAllByIdType($meaningId, self::TYPE_MEANING);
  }

  static function getTreeMentions($treeId) {
    return self::getAllByIdType($treeId, self::TYPE_TREE);
  }

  // Get detailed tree mentions about a tree, including origin tree and meaning.
  // If $treeId is null, get detailed tree mentions about all trees.
  static function getDetailedTreeMentions($treeId = null) {
    $query = Model::factory('Mention')
           ->table_alias('m')
           ->select('mean.htmlRep')
           ->select('mean.breadcrumb')
           ->select('src.id', 'srcId')
           ->select('src.description', 'srcDesc')
           ->select('dest.id', 'destId')
           ->select('dest.description', 'destDesc')
           ->join('Meaning', ['m.meaningId', '=', 'mean.id'], 'mean')
           ->join('Tree', ['mean.treeId', '=', 'src.id'], 'src')
           ->join('Tree', ['m.objectId', '=', 'dest.id'], 'dest')
           ->where('m.objectType', Mention::TYPE_TREE);

    if ($treeId) {
      $query = $query->where('dest.id', $treeId);
    }

    return $query->find_many();
  }

  // Get detailed meaning mentions about any meaning inside this tree.
  static function getDetailedMeaningMentions($treeId) {
    return Model::factory('Mention')
      ->table_alias('m')
      ->select('msrc.htmlRep', 'srcRep')
      ->select('msrc.breadcrumb', 'srcBreadcrumb')
      ->select('mdest.breadcrumb', 'destBreadcrumb')
      ->select('tsrc.id', 'tsrcId')
      ->select('tsrc.description', 'tsrcDesc')
      ->join('Meaning', ['m.meaningId', '=', 'msrc.id'], 'msrc')
      ->join('Tree', ['msrc.treeId', '=', 'tsrc.id'], 'tsrc')
      ->join('Meaning', ['m.objectId', '=', 'mdest.id'], 'mdest')
      ->join('Tree', ['mdest.treeId', '=', 'tdest.id'], 'tdest')
      ->where('m.objectType', Mention::TYPE_MEANING)
      ->where('tdest.id', $treeId)
      ->find_many();
  }

  // Deletes the old mentions and adds the new mentions
  static function wipeAndRecreate($meaningId, $objectType, $objectIds) {
    self::delete_all_by_meaningId_objectType($meaningId, $objectType);

    foreach ($objectIds as $oId) {
      $m = Model::factory('Mention')->create();
      $m->meaningId = $meaningId;
      $m->objectId = $oId;
      $m->objectType = $objectType;
      $m->save();
    }
  }
}

?>
