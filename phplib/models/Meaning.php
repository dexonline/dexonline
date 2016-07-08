<?php

class Meaning extends BaseObject implements DatedObject {
  public static $_table = 'Meaning';

  /**
   * Convert a tree produced by the tree editor to the format used by loadTree.
   * We need this in case validation fails and we cannot save the tree, so we need to display it again.
   **/
  static function convertTree($meanings) {
    $meaningStack = [];
    $results = [];

    foreach ($meanings as $tuple) {
      $row = [];
      $m = $tuple->id
         ? self::get_by_id($tuple->id)
         : Model::factory('Meaning')->create();

      $m->internalRep = $tuple->internalRep;
      $m->htmlRep = AdminStringUtil::htmlize($m->internalRep, 0);
      $m->internalEtymology = $tuple->internalEtymology;
      $m->htmlEtymology = AdminStringUtil::htmlize($m->internalEtymology, 0);
      $m->internalComment = $tuple->internalComment;
      $m->htmlComment = AdminStringUtil::htmlize($m->internalComment, 0);

      $row['meaning'] = $m;
      $row['sources'] = Source::loadByIds($tuple->sourceIds);
      $row['tags'] = Tag::loadByIds($tuple->tagIds);
      $row['relations'] = Relation::loadRelatedLexems($tuple->relationIds);
      $row['children'] = [];

      if ($tuple->level) {
        $meaningStack[$tuple->level - 1]['children'][] = &$row;
      } else {
        $results[] = &$row;
      }
      $meaningStack[$tuple->level] = &$row;
      unset($row);
    }

    return $results;
  }

  /* Save a tree produced by the tree editor in editTree.php */
  static function saveTree($meanings, $tree) {
    $seenMeaningIds = [];

    // Keep track of the previous meaning ID at each level. This allows us
    // to populate the parentId field
    $meaningStack = [];
    $displayOrder = 1;
    foreach ($meanings as $tuple) {
      $m = $tuple->id
         ? self::get_by_id($tuple->id)
         : Model::factory('Meaning')->create();
      $m->parentId = $tuple->level ? $meaningStack[$tuple->level - 1] : 0;
      $m->displayOrder = $displayOrder++;
      $m->breadcrumb = $tuple->breadcrumb;
      $m->userId = session_getUserId();
      $m->treeId = $tree->id;
      $m->internalRep = $tuple->internalRep;
      $m->htmlRep = AdminStringUtil::htmlize($m->internalRep, 0);
      $m->internalEtymology = $tuple->internalEtymology;
      $m->htmlEtymology = AdminStringUtil::htmlize($m->internalEtymology, 0);
      $m->internalComment = $tuple->internalComment;
      $m->htmlComment = AdminStringUtil::htmlize($m->internalComment, 0);
      $m->save();
      $meaningStack[$tuple->level] = $m->id;

      MeaningSource::updateList(['meaningId' => $m->id], 'sourceId', $tuple->sourceIds);
      MeaningTag::updateList(['meaningId' => $m->id], 'tagId', $tuple->tagIds);
      foreach ($tuple->relationIds as $type => $lexemIds) {
        if ($type) {
          Relation::updateList(['meaningId' => $m->id, 'type' => $type],
                               'lexemId', $lexemIds);
        }
      }
      $seenMeaningIds[] = $m->id;
    }
    self::deleteNotInSet($seenMeaningIds, $tree->id);
  }

  /* Deletes all the meanings associated with $treeId that aren't in the $meaningIds set */
  public static function deleteNotInSet($meaningIds, $treeId) {
    $meanings = self::get_all_by_treeId($treeId);
    foreach ($meanings as $m) {
      if (!in_array($m->id, $meaningIds)) {
        $m->delete();
      }
    }
  }

  public function delete() {
    MeaningSource::deleteByMeaningId($this->id);
    MeaningTag::deleteByMeaningId($this->id);
    Relation::delete_all_by_meaningId($this->id);
    parent::delete();
  }
}

?>
